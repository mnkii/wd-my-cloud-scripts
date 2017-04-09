#!/usr/bin/env php
<?php
/**
 * Script to find files with duplicate content in the given file path
 * For more details, run:
 *     php duplicate-file-finder.php -h
 *
 * Written to work on a WD My Cloud NAS, but run it anywhere you wish.
 *
 * @see https://github.com/mnkii/wd-my-cloud-scripts
 */
if (count(getopt('h', ['help'])) > 0 || count($argv) !== 2) {
    echo <<<EOD
Usage: php duplicate-file-finder.php PATH

Recursively searches PATH for files with duplicate content. Writes found
duplicates in CSV format to stdout, with all occurrence of the file
appearing across the row. Symlinks are not followed.

If the search PATH is large then you may want to run with nohup or similar.

Written to work on a WD My Cloud NAS, but run it anywhere you wish.

Uses md5 of file contents to compare content.

For more details, see the README.md

EOD;
    exit;
}

$filePath = $argv[1];
if (!is_dir($filePath)) {
	printf('%s is not a directory' . PHP_EOL, $filePath);
    exit(1);
}

$dbFile = tempnam('/tmp', 'db-duplicate-file-finder.');
$db = new SQLite3($dbFile);
$db->exec('CREATE TABLE hashes (file TEXT, hash TEXT)');

$iterator = new RecursiveDirectoryIterator($filePath);
foreach (new RecursiveIteratorIterator($iterator) as $file) {
    if (is_file($file) && !is_link($file)) {
        $stmt = $db->prepare('INSERT INTO hashes (file, hash) VALUES (:file, :hash)');
        $stmt->bindValue(':file', realpath($file), SQLITE3_TEXT);
        $stmt->bindValue(':hash', md5_file($file), SQLITE3_TEXT);
        $stmt->execute();
    }
}

$results = $db->query('
    SELECT GROUP_CONCAT(\'"\' || REPLACE(file, \'"\', \'""\') || \'"\', \',\') AS files
      FROM hashes
  GROUP BY hash HAVING COUNT(hash) > 1
     ORDER BY file ASC
');

while ($row = $results->fetchArray()) {
    echo $row['files'] . PHP_EOL;
}

unlink($dbFile);

#!/usr/bin/env php
<?php
/**
 * Script to find files in SOURCE but not in DESTINATION, regardless of file name
 * For help, run:
 *     php find-not-yet-copied-to-my-cloud -h
 *
 * Written to work on a WD My Cloud NAS, but run it anywhere you wish.
 *
 * @see https://github.com/mnkii/wd-my-cloud-scripts
 */
if (count(getopt('h', ['help'])) > 0 || count($argv) !== 3) {
    echo <<<EOD
Usage: php find-not-yet-copied-to-my-cloud.php SOURCE DESTINATION

Gets a list of files in SOURCE which are not in DESTINATION.

Searches each directory recursively. Searches files by content, not by name.

Written to work on a WD My Cloud NAS, but run it anywhere you wish.

If SOURCE or DESTINATION is large then you may want to run with nohup or similar.

Uses md5 of file contents to compare content.

For more details, see the README.md

EOD;
    exit;
}

$source = rtrim($argv[1], '/');
$destination = rtrim($argv[2], '/');
if (!is_dir($source)) {
    printf('%s is not a directory' . PHP_EOL, $source);
    exit(1);
}
if (!is_dir($destination)) {
  printf('%s is not a directory' . PHP_EOL, $destination);
    exit(1);
}

$dbFile = tempnam('/tmp', 'db-duplicate-file-finder.');

$db = new SQLite3($dbFile);
$db->exec('CREATE TABLE hashes (file TEXT, hash TEXT, source BOOLEAN DEFAULT 0)');

$iterator = new RecursiveDirectoryIterator($source);
foreach (new RecursiveIteratorIterator($iterator) as $file) {
    if (is_file($file) && !is_link($file)) {
        $stmt = $db->prepare('INSERT INTO hashes (file, hash, source) VALUES (:file, :hash, 1)');
        $stmt->bindValue(':file', realpath($file), SQLITE3_TEXT);
        $stmt->bindValue(':hash', md5_file($file), SQLITE3_TEXT);
        $stmt->execute();
    }
}

$iterator = new RecursiveDirectoryIterator($destination);
foreach (new RecursiveIteratorIterator($iterator) as $file) {
    if (is_file($file) && !is_link($file)) {
        $stmt = $db->prepare('INSERT INTO hashes (file, hash, source) VALUES (:file, :hash, 0)');
        $stmt->bindValue(':file', realpath($file), SQLITE3_TEXT);
        $stmt->bindValue(':hash', md5_file($file), SQLITE3_TEXT);
        $stmt->execute();
    }
}

$results = $db->query('
    SELECT file
      FROM hashes
     WHERE source = 1
       AND hash NOT IN (
           SELECT hash FROM hashes WHERE source = 0
       )
  ORDER BY file ASC
');

while ($row = $results->fetchArray()) {
    echo $row['file'] . PHP_EOL;
}

unlink($dbFile);
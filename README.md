## Synopsis

A few scripts to help me manage files on my WD My Cloud NAS.

These scripts came about as since V4 of the Western Digital My Cloud firmware, running apt-get can brick your NAS, meaning you can't safely install any software to help remove duplicate files, etc.

They are designed to be simple stand alone utilities which you can download and execute without installing any dependencies on your My Cloud.

**DISCLAIMER: This scripts were written for me to help manage my own media, use at your own risk. As always, cross check and triple check before you delete anything! (And they use md5 for file comparison!)**

## Installation And General Usage

You will need to [enable SSH on your My Cloud](http://support.wdc.com/knowledgebase/answer.aspx?ID=14946).

To install, just copy these scripts onto your My Cloud any way you want (use a share, wget etc).

Run these scripts directly on your My Cloud using ssh. Some commands can take a long time to run if there is a large amount of data to search through, so you probably want to keep them running even after you have killed an ssh session. Screen and tmux are not installed, so the easiest way is to use nohup:


```bash
# Run duplicate file finder script in the background even after you have terminated the ssh session
nohup php duplicate-file-finder.php /shares &

# See if the script is still running
ps aux | grep duplicate-file-finder.php

# View the command output, it is written into nohup.out
cat nohup.out
```

## The Scripts

### Find Files With Duplicate Content: duplicate-file-finder.php

Usage: `php duplicate-file-finder.php PATH`

Recursively searches PATH (without following symlinks) for files with identical content, regardless of filename. Writes found duplicates in CSV format to stdout, with all occurrence of the file appearing across the row. You can then import the output to Excel, pipe to xargs etc.

Example: `php duplicate-file-finder.php /shares`

### Find files which you have not yet copied onto your NAS: find-not-yet-copied-to-my-cloud.php

Usage: `php find-not-yet-copied-to-my-cloud.php SOURCE DESTINATION`

Prints a list of files in SOURCE but not in DESTINATION to stdout. Searches by file content rather than file name. Usefull for getting a list of files on a USB device (such as your cameras SD card, SOURCE) plugged directly into the NAS, which you have not yet copied onto you NAS (DESTINATION).

Example: `php find-not-yet-copied-to-my-cloud.php /var/media/USB_Storage/ /shares`

### Find all files with identical content to a given file: find-file-by-content.sh

Usage: `bash find-file-by-content.sh NEEDLE HAYSTACK`

Prints all files in directory or subdirectory of HAYSTACK which have identical content to the file NEEDLE to stdout. Usefull if you are sure you have copied a file onto your NAS, renamed it, but then are no longer able to find it.

Example: `bash find-file-by-content.sh /var/media/USB_Storage/DCIM/100CANON/IMG_2935.JPG /shares/`

#!/usr/bin/env bash
#
# Finds files in HAYSTACK which have identical content to NEEDLE
# For more details, run:
#     bash find-file-by-content.sh -h
#
# Written to work on a WD My Cloud NAS, but run it anywhere you wish.
#
# @see https://github.com/mnkii/wd-my-cloud-scripts
#
usage="Usage: php find-file-by-content.sh NEEDLE HAYSTACK

Recursively searches directory HAYSTACK for files with identical content to the file NEEDLE. Writes found duplicates to stdout. Will return NEEDLE as a match if it is in HAYSTACK.

Written to work on a WD My Cloud NAS, but run it anywhere you wish.

If the search HAYSTACK is large then you may want to run with nohup or similar.

Uses md5 of file contents to compare content.

For more details, see the README.md"

while getopts 'h' option; do
  case "$option" in
    h) echo "$usage"
       exit
       ;;
  esac
done

if [ "$#" -ne 2 ]; then
    echo "$usage"
    exit
fi

if [ ! -f "$1" ]; then
    echo "File '$1' does not exist"
    exit 1;
fi

if [ ! -d "$2" ]; then
    echo "'$2' is not a directory"
    exit 1;
fi

needle=$(md5sum "$1" | awk '{ print $1 }')

find "$2" -type f -exec md5sum "{}" + | grep $needle | awk '{ print $2 }'

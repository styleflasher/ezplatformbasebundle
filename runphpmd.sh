#!/bin/bash
# replace space by comma in file list
FILELIST=`echo $@ | sed 's/ /,/g'`
phpmd $FILELIST text phpmd-rule.xml

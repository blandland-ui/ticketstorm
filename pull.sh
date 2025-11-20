#!/bin/bash

git fetch origin
git pull origin master
RESULT=?

if [ $RESULT -eq 0 ]; then
	echo "PULL SUCCESS!"
else 
	echo "PULL FAILURE!!!"
	git reset --hard origin/main
fi

read -p "Press enter... ..."
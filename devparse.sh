#!/bin/bash

for fil in $(ls /dev/sd[a-z][1-9]); do
	df -h $fil | sed -r 's!^[^/]+!!' | sed '/^$/d';
done;
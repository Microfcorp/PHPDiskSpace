#!/bin/bash

for fil in $(ls /dev/sd*);
do
df -h $fil | sed -r 's!^[^/]+!!' | sed '/^$/d';
done;
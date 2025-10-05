#!/bin/sh
set -e

openssl base64 -A -in $1
echo "\n"

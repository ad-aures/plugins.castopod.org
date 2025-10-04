#!/bin/sh
set -e

# install only production dependencies using the --no-dev option
composer install --no-dev --prefer-dist --no-ansi --no-interaction --no-progress --ignore-platform-reqs

# build production static assets (css, js, images, icons, fonts, etc.)
pnpm run build

# create bundle: uses .rsync-filter (-F) file to copy only needed files
rsync -aF --progress . ./dist

#!/bin/bash

sudo cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
sudo cp ./.devcontainer/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

php -S 0.0.0.0:80 -t $(pwd)
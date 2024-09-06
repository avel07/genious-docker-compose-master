#!/usr/bin/env bash
set -e -u

# Директория текущего файла
SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )

# Color variables
bg_green='\033[0;42m'
c_cyan='\e[96m'
bg_clear='\033[0m'

# mysql container files
echo -e "${c_cyan}Исправляем права на файлы...${bg_clear}"

echo -e "${c_cyan}Исправляем права mysql...${bg_clear}"
[ -d $SCRIPT_DIR/../logs/mysql ] && [ ! -f $SCRIPT_DIR/../logs/mysql/error.log ] && touch $SCRIPT_DIR/../logs/mysql/error.log
chown -R 1001:1001 $SCRIPT_DIR/../logs/mysql
[ -d $SCRIPT_DIR/../private/mysql-data ] && chown -R 1001:1001 $SCRIPT_DIR/../private/mysql-data
[ -d $SCRIPT_DIR/../private/mysqld ] && chown -R 1001:1001 $SCRIPT_DIR/../private/mysqld

# php and nginx containers files
echo -e "${c_cyan}Исправляем права nginx и php...${bg_clear}"
[ -d $SCRIPT_DIR/../logs/nginx ] && chown -R 1000:1000  $SCRIPT_DIR/../logs/nginx
[ -d $SCRIPT_DIR/../logs/php ] && chown -R 1000:1000  $SCRIPT_DIR/../logs/php
[ -d  $SCRIPT_DIR/../nginx/sites ] && chown -R 1000:1000  $SCRIPT_DIR/../nginx/sites
echo -e "${c_cyan}Исправляем права web директорий...${bg_clear}"
chown -R 1000:1000 $SCRIPT_DIR/../../web

echo -e "${bg_green}Исправление прав завершено!${bg_clear}"
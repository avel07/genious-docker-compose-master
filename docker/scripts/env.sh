#!/usr/bin/env bash
set -e -u

# Флаг p - production
# В продакшен есть отличия запуска
production=false
while getopts ":p" opt; do
    case $opt in
        p)  production=true ;;
        \?) echo "invalid option: -$OPTARG." && exit 1 ;;
    esac
done


# Директория текущего файла
SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )

# Если это прод, используем файл для прода
DOCKER_COMPOSE_DEV="docker compose --env-file=$SCRIPT_DIR/../../.env --file=docker-compose.dev.yml"
if ($production); then
    DOCKER_COMPOSE="docker compose --env-file=$SCRIPT_DIR/../../.env  --file=docker-compose.yml"
else
    DOCKER_COMPOSE=$DOCKER_COMPOSE_DEV
fi

# Color variables
bg_red='\033[0;41m'
bg_green='\033[0;42m'
bg_cyan='\033[0;46m'
c_cyan='\e[96m'
bg_clear='\033[0m'

# Проверяем наличие нужных утилит, сервсивов для запуска сервера
checkUtils()
{
    echo -e "${bg_cyan}Проверяем установленные утилиты...${bg_clear}" ;

    if  [ ! -x "$(which git)" ]; then
        echo -e "${bg_red}Git не установлен!${bg_clear}" >&2;
        echo "Как установить (git): https://www.digitalocean.com/community/tutorials/how-to-install-git-on-ubuntu-22-04"
        exit 1 # terminate and indicate error
    fi

    if [ ! -x "$(which docker)" ] ; then
        echo -e "${bg_red}Docker не установлен!${bg_clear}" >&2;
        echo "Как установить (docker): https://www.digitalocean.com/community/tutorials/how-to-install-and-use-docker-on-ubuntu-22-04"
        echo "Как установить (docker compose plugin): https://www.digitalocean.com/community/tutorials/how-to-install-and-use-docker-compose-on-ubuntu-22-04"
        exit 1 # terminate and indicate error
    fi

    if ( ! docker compose version > /dev/null 2>&1 ); then
        echo -e "${bg_red}Docker compose plugin не установлен!${bg_clear}" >&2;
        echo "Как установить (docker compose plugin): https://www.digitalocean.com/community/tutorials/how-to-install-and-use-docker-compose-on-ubuntu-22-04"
        exit 1 # terminate and indicate error
    fi
}

# Установка nuxt зависимостей
# installNuxt()
# {
#     if [ -d $SCRIPT_DIR/../../web/nuxt ]; then
#         echo -e "${bg_cyan}Устанавливаем зависимости nuxt...${bg_clear}";
#         # Собираем через DEV

#         if $DOCKER_COMPOSE_DEV run --rm nuxt npm install --no-update-notifier;
#         then
#             echo "Зависимости nuxt успешно установлены."

#             if ($production && ! $DOCKER_COMPOSE_DEV run --rm nuxt npm run build); then
#                 echo -e "${bg_red}Произошла ошибка при build сборке nuxt!${bg_clear}" >&2;  
#                 exit 1
#             fi
#         else
#             echo -e "${bg_red}Произошла ошибка при установке зависимостей!${bg_clear}" >&2;
#             exit 1
#         fi
#     else 
#         echo -e "${bg_red}Директория с nuxt не существует!${bg_clear}" >&2;
#         exit 1
#     fi
# }

# Bitrix composer и миграции
bitrixComposer()
{    
    echo -e "${bg_cyan}Выполняем миграции, устанавливаем зависимости composer...${bg_clear}"
    $DOCKER_COMPOSE run --rm php bash -c '
            composer install -d /web/bitrix/local &&
            php -d short_open_tag=1 /web/bitrix/bitrix/modules/sprint.migration/tools/migrate.php ls --new &&
            php -d short_open_tag=1 /web/bitrix/bitrix/modules/sprint.migration/tools/migrate.php up
        '
}

# Вывод значения парметра .env
printValueEnv()
{
    echo $(cat $SCRIPT_DIR/../../.env | sed -En "s/$1=\"(.+)\"/\1 /gp")
}

# Вывод значений для доступа к БД
bitrixPrintDBSettings()
{
    echo -e "${bg_green}Данные для подключения базы данных:${bg_clear}";
    echo -e "${c_cyan}Host:${bg_clear} db";
    echo -e "${c_cyan}Database:${bg_clear} $(printValueEnv MYSQL_DATABASE)";
    echo -e "${c_cyan}Login:${bg_clear} $(printValueEnv MYSQL_USER)";
    echo -e "${c_cyan}Password:${bg_clear} $(printValueEnv MYSQL_PASSWORD)";
    echo -e "${c_cyan}Password root:${bg_clear} $(printValueEnv MYSQL_ROOT_PASSWORD)";
}

# Старт контейнеров и проверка установки bitrix
start()
{
    $DOCKER_COMPOSE down;
    echo -e "${bg_cyan}Запускаем контейнеры...${bg_clear}";
    if $DOCKER_COMPOSE up -d --build ; then

        sleep 5; # TODO: ждем пока БД поднимется
        if (docker compose logs 2>&1 | grep 'Permission denied') ; then
            echo -e "${bg_red}Проблемы с запуском контейнеров. Права на файлы необходимо исправить!${bg_clear}" >&2;

            read -r -p "Пробуем исправить (нужен sudo)? [y/N] " response
            case "$response" in
                [yY][eE][sS]|[yY]) 
                    sudo bash $SCRIPT_DIR/fix-right.sh
                    ;;
                *)
                    echo -e "${bg_red}Отключаем контейнеры. Испраьте права (make fix) и запустите снова${bg_clear}";
                    $DOCKER_COMPOSE down
                    exit 1;
                    ;;
            esac
            sleep 7;
        fi
    
        if (bitrixComposer); then
            echo -e "\n";
            echo -e "${bg_green}Контейнеры успешно запущены:${bg_clear}";
            echo -e "${c_cyan}---------------------------------------${bg_clear}";
            echo -e "${c_cyan}Backend (bitrix):${bg_clear}  $(printValueEnv DOMAIN_NAME_BACKEND)";
            echo -e "${c_cyan}Frontend (nuxt):${bg_clear}   $(printValueEnv DOMAIN_NAME_FRONTEND)";
            echo -e "${c_cyan}---------------------------------------${bg_clear}";
            exit 0;            
        else
            echo -e "\n";
            echo -e "${c_cyan}---------------------------------------${bg_clear}";
            echo -e "${c_cyan}Bitrix не установлен. \nСкачиваем restore.php${bg_clear}" >&2;
            wget -q --show-progress http://www.1c-bitrix.ru/download/scripts/restore.php -O ./../web/bitrix/restore.php
            echo -e "${c_cyan}---------------------------------------${bg_clear}";
            echo -e "${c_cyan}Восстановите резервную копию сайта:${bg_clear} http://localhost/restore.php" >&2;
            echo -e "${c_cyan}---------------------------------------${bg_clear}";
            bitrixPrintDBSettings
            echo -e "${c_cyan}---------------------------------------${bg_clear}";
            exit 0;
        fi
    fi
}

# Этапы установки
cd $SCRIPT_DIR/..
checkUtils
start
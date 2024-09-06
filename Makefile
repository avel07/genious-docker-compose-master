project_name = bitrix-workspace

.PHONY: help
help: ## Отобразить все команды
	@grep -E '^[a-z.A-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

up:	## Поднять docker без сборки (без миграций, зависимостей и прочего)
	cd ./docker && docker compose --file="docker-compose.dev.yml" --env-file=../.env up -d

start: ## Поднять docker compose для локальной разработки (bitrix - localhost:80, nuxt - localhost:3000)
	bash ./docker/scripts/env.sh

stop: ## Отключаем все сервисы
	cd ./docker && docker compose --env-file=../.env down

prod: ## Поднять продакшн
	bash ./docker/scripts/env.sh -p

shell: ## Открыть консоль в контейнере php (bitrix)
	cd ./docker && docker compose --env-file=../.env exec php /bin/sh

shell-nuxt: ## Открыть консоль в контейнере nuxt
	cd ./docker && docker compose --env-file=../.env exec nuxt /bin/sh

fix: ## Исправляет права на файлы (нужен sudo)
	cd ./docker && bash ./scripts/fix-right.sh

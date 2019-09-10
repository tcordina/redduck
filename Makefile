DOCKER_COMPOSE?=docker-compose
EXEC?=$(DOCKER_COMPOSE) exec app
CONSOLE=php bin/console

.DEFAULT_GOAL := help
.PHONY: help start stop restart install uninstall reset clear-cache shell clear clean
.PHONY: build up perm

help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

##
## Project setup
##---------------------------------------------------------------------------

start:                                                                                                 ## Start docker containers
	$(DOCKER_COMPOSE) start

stop:                                                                                                  ## Stop docker containers
	$(DOCKER_COMPOSE) stop

restart:                                                                                               ## Restart docker containers
	$(DOCKER_COMPOSE) restart

clear-cache: perm
	$(EXEC) $(CONSOLE) cache:clear --no-warmup
	$(EXEC) $(CONSOLE) cache:warmup

shell:                                                                                                 ## Run app container in interactive mode
	$(EXEC) /bin/sh

clear: perm                                                                                            ## Remove cache,logs and sessions
	$(EXEC) rm -rf var/cache/*
	rm -rf var/log/*
	rm -rf public/build
	rm -f var/.php_cs.cache


# Internal rules

build:
	$(DOCKER_COMPOSE) pull --ignore-pull-failures
	$(DOCKER_COMPOSE) build --force-rm

up:
	$(DOCKER_COMPOSE) up -d --remove-orphans

perm:
	$(EXEC) chmod -R 777 var vendor
	$(EXEC) chown -R www-data:root var vendor


# Rules from files

vendor: composer.lock
	$(EXEC) composer install -n

composer.lock: composer.json
	@echo compose.lock is not up to date.
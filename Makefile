DOCKER_ENABLED=1
PHP_CS_FIXER_CONFIGURATION_FILE=./.lint/.php-cs-fixer.php
PHPSTAN_CODE_PATH=./src ./.aatis
PHPSTAN_CONFIGURATION_FILE=./.lint/aatis.neon

AATIS_MODULES=$(shell cat aatis.modules.commit)

include .boing/makes/aatis.mk

# Get extra arguments
args = `arg="$(filter-out $@,$(MAKECMDGOALS))" && echo $${arg:-${1}}`

# Allow to run command with extra arguments
%:
	@:

up:
	@git fetch -p
	@git checkout develop
	@git pull origin develop -f
	@cd .aatis; \
	for module in ${AATIS_MODULES}; do \
		cd "$${module}"; \
		git checkout develop; \
		git fetch -p; \
		git pull origin develop -f; \
		cd ../; \
	done
.PHONY: up

commit:
	@cd .aatis; \
	for module in ${AATIS_MODULES}; do \
		cd "$${module}"; \
		git checkout develop; \
		git commit -m "${args}"; \
		git push origin develop; \
		cd ../; \
	done
.PHONY: commit

amend:
	@cd .aatis; \
	for module in ${AATIS_MODULES}; do \
		cd "$${module}"; \
		git checkout develop; \
		git add .; \
		git commit --amend --no-edit; \
		git push origin develop -f; \
		cd ../; \
	done
.PHONY: commit

amend-message:
	@cd .aatis; \
	for module in ${AATIS_MODULES}; do \
		cd "$${module}"; \
		git checkout develop; \
		git commit --amend -m "${args}"; \
		git push origin develop -f; \
		cd ../; \
	done
.PHONY: commit

up-modules:
	@git checkout develop
	@git pull origin develop
	@-git submodule update --remote >> /dev/null
	@git add .
	@git commit -m ":arrow_up: Update modules"
	@git push origin develop
.PHONY: up-modules

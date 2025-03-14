DOCKER_ENABLED=1
PHP_CS_FIXER_CONFIGURATION_FILE=./.lint/.php-cs-fixer.php
PHPSTAN_CODE_PATH=./src ./.aatis
PHPSTAN_CONFIGURATION_FILE=./.lint/aatis.neon

AATIS_MODULES=$(shell cat aatis.modules.commit)

include .boing/makes/aatis.mk

commit:
	@cd .aatis; \
	for module in ${AATIS_MODULES}; do \
		echo "$${module}"; \
		cd "$${module}"; \
		git checkout develop; \
		git add .; \
		git commit -m ":fire: Remove composer.lock"; \
		git push origin develop; \
		cd ../; \
	done
.PHONY: commit

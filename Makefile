DOCKER_ENABLED=1
PHP_CS_FIXER_CONFIGURATION_FILE=./.lint/.php-cs-fixer.php
PHPSTAN_CODE_PATH=./src ./.aatis
PHPSTAN_CONFIGURATION_FILE=./.lint/aatis.neon

AATIS_MODULES=$(shell cat aatis.modules)

include .boing/makes/aatis.mk

test:
	cd .aatis
	@for module in ${AATIS_MODULES}; do \
		echo "$${module}"; \
		cd "$${module}"; \
		git checkout -b temp; \
		git add .; \
		git commit -m "temp"; \
		git push origin temp; \
		cd ../; \
	done
.PHONY: test

DOCKER_ENABLED=1
PHP_CS_FIXER_CONFIGURATION_FILE=./.lint/.php-cs-fixer.php
PHPSTAN_CODE_PATH=./src ./.aatis

include .boing/makes/aatis.mk

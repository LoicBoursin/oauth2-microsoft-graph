$SHELL := /bin/bash

EXEC_PHP                 = php

# Executables
PHPSTAN                  = ./vendor/bin/phpstan
PHP_CS_FIXER             = ./vendor/bin/php-cs-fixer
PHPUNIT                  = ./vendor/bin/phpunit

lint-php: ## Lint files with php-cs-fixer
	$(PHP_CS_FIXER) fix --dry-run --allow-risky=yes -v
.PHONY: lint-php

fix-php: ## Fix
	$(PHP_CS_FIXER) fix --allow-risky=yes
.PHONY: fix-php

stan: # Run PHPStan
	$(PHPSTAN) analyse -c phpstan.neon --memory-limit 1G
.PHONY: stan

test: ## Run tests
	$(PHPUNIT) --coverage-text
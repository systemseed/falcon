# Define here list of available make commands.
.PHONY: default pull up stop down clean exec exec\:wodby exec\:root drush \
prepare install add\:additional-modules \
code\:check code\:fix \
tests\:prepare tests\:run tests\:cli tests\:autocomplete

# Create local environment files.
$(shell cp -n \.\/\.docker\/docker-compose\.override\.default\.yml \.\/\.docker\/docker-compose\.override\.yml)
# If .env file doesn't exist yet - copy it from the default one.
# Then if OS is Linux we change the PHP_TAG:
#  - uncomment all the strings containing 'PHP_TAG'
#  - comment all the strings containing 'PHP_TAG' and '-dev-macos-'
$(shell ! test -e \.env && cp \.env\.default \.env && uname -s | grep -q 'Linux' && sed -i '/PHP_TAG/s/^# //g' \.env && sed -i -E '/PHP_TAG.+-dev-macos-/s/^/# /g' \.env)

include .env

# Define function to highlight messages.
# @see https://gist.github.com/leesei/136b522eb9bb96ba45bd
cyan = \033[38;5;6m
bold = \033[1m
reset = \033[0m
message = @echo "${cyan}${bold}${1}${reset}"

# Define 3 users with different permissions within the container.
# docker-www-data is applicable only for php container.
docker-www-data = docker-compose exec --user=82:82 $(firstword ${1}) time -f"%E" sh -c "$(filter-out $(firstword ${1}), ${1})"
docker-wodby = docker-compose exec $(firstword ${1}) time -f"%E" sh -c "$(filter-out $(firstword ${1}), ${1})"
docker-root = docker-compose exec --user=0:0 $(firstword ${1}) time -f"%E" sh -c "$(filter-out $(firstword ${1}), ${1})"

default: up

pull:
	$(call message,$(PROJECT_NAME): Updating Docker images)
	docker-compose pull
	docker pull $(DOCKER_PHPCS)
	docker pull $(DOCKER_ESLINT)

up:
	$(call message,$(PROJECT_NAME): Build and run containers)
	docker-compose up -d --remove-orphans

stop:
	$(call message,$(PROJECT_NAME): Stopping containers)
	docker-compose stop

down:
	$(call message,$(PROJECT_NAME): Removing network & containers)
	docker-compose down -v --remove-orphans

clean: | up
	$(call message,$(PROJECT_NAME): Removing vendor and web directories)
	$(call docker-root, php rm -rf vendor)
	$(call docker-root, php rm -rf web)
	@$(MAKE) -s down

exec:
    # Remove the first argument from the list of make commands.
	$(eval ARGS := $(filter-out $@,$(MAKECMDGOALS)))
	$(eval TARGET := $(firstword $(ARGS)))
	docker-compose exec --user=82:82 $(TARGET) sh

exec\:wodby:
    # Remove the first argument from the list of make commands.
	$(eval ARGS := $(filter-out $@,$(MAKECMDGOALS)))
	$(eval TARGET := $(firstword $(ARGS)))
	docker-compose exec $(TARGET) sh

exec\:root:
    # Remove the first argument from the list of make commands.
	$(eval ARGS := $(filter-out $@,$(MAKECMDGOALS)))
	$(eval TARGET := $(firstword $(ARGS)))
	docker-compose exec --user=0:0 $(TARGET) sh

drush:
    # Remove the first argument from the list of make commands.
	$(eval COMMAND_ARGS := $(filter-out $@,$(MAKECMDGOALS)))
	$(call message,Executing \"drush -r web $(COMMAND_ARGS) --yes\")
	$(call docker-www-data, php drush -r web $(COMMAND_ARGS) --yes)

prepare: | up
    # Prepare composer dependencies.
	$(call message,$(PROJECT_NAME): Installing/updating composer dependencies)
	-$(call docker-wodby, php composer install --no-suggest)
    # Prepare public files folder.
	$(call message,$(PROJECT_NAME): Preparing public files directory)
	$(call docker-wodby, php mkdir -p web/sites/default/files)
	$(call docker-root, php chown -R www-data: web/sites/default/files)
    # Prepare settings.php file.
	$(call message,$(PROJECT_NAME): Making settings.php writable)
	$(call docker-wodby, php chmod 666 web/sites/default/settings.php)
    # Prepare git hooks.
	$(call message,$(PROJECT_NAME): Setting up git hooks)
	ln -sf $(shell pwd)/.git-hooks/* $(shell pwd)/.git/hooks

install: | prepare
	$(call message,$(PROJECT_NAME): Installing Drupal)
	sleep 5
	$(call docker-www-data, php drush -r web site-install falcon \
		--db-url=mysql://$(DB_USER):$(DB_PASSWORD)@$(DB_HOST)/$(DB_NAME) --site-name=$(PROJECT_NAME) --account-pass=admin \
		install_configure_form.enable_update_status_module=NULL --yes)
	@if [ $(ENVIRONMENT) = "development" ]; then \
		$(MAKE) -s drush en $(DEVELOPMENT_MODULES); \
		$(call docker-wodby, php cp web/sites/example.settings.local.php web/sites/default/settings.local.php); \
		$(call docker-wodby, php sed -i \"/settings.local.php';/s/# //g\" web/sites/default/settings.php); \
	fi
	$(call message,Congratulations! You installed $(PROJECT_NAME)!)

add\:additional-modules:
	$(call message,$(PROJECT_NAME): Installing additional modules)
	$(MAKE) -s drush en $(ADDITIONAL_MODULES)

code\:check:
    # PHP coding standards check.
	$(call message,$(PROJECT_NAME): Checking PHP for compliance with Drupal coding standards...)
	docker run --rm \
		-v $(shell pwd)/modules:/app/modules $(DOCKER_PHPCS) phpcs \
		-s --colors --warning-severity=0 --standard=Drupal,DrupalPractice .
    # Javascript coding standards check.
	$(call message,$(PROJECT_NAME): Checking Javascript for compliance with Drupal coding standards...)
	docker run --rm \
		-v $(shell pwd)/modules:/eslint/modules \
		-v $(shell pwd)/.eslintrc.json:/eslint/.eslintrc.json \
		$(DOCKER_ESLINT) .

code\:fix:
	$(call message,$(PROJECT_NAME): Auto-fixing coding style issues...)
	docker run --rm \
		-v $(shell pwd)/modules:/app/modules $(DOCKER_PHPCS) phpcbf \
		-s --colors --warning-severity=0 --standard=Drupal,DrupalPractice .
	docker run --rm \
		-v $(shell pwd)/modules:/eslint/modules \
		-v $(shell pwd)/.eslintrc.json:/eslint/.eslintrc.json \
		$(DOCKER_ESLINT) --fix .

tests\:prepare:
	$(call message,$(PROJECT_NAME): Preparing Codeception framework for testing...)
	docker-compose run --rm codecept build

tests\:run:
	$(call message,$(PROJECT_NAME): Run Codeception tests)
	$(eval ARGS := $(filter-out $@,$(MAKECMDGOALS)))
	docker-compose run --rm codecept run $(ARGS) --debug

tests\:cli:
	$(call message,$(PROJECT_NAME): Open Codeception container CLI)
	docker-compose run --rm --entrypoint bash codecept

tests\:autocomplete:
	$(call message,$(PROJECT_NAME): Copy Codeception code in .codecept folder to enable IDE autocomplete)
	rm -rf .codecept
	docker cp $(PROJECT_NAME)_codecept:/repo/ .codecept
	rm -rf .codecept/.git

# https://stackoverflow.com/a/6273809/1826109
%:
	@:

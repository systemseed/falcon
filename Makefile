# Define here list of available make commands.
.PHONY: default pull up stop down clean drush \
exec exec\:wodby exec\:root prepare install \
code\:check code\:fix \
tests\:prepare tests\:codeception tests\:codeception\:cli \
tests\:testcafe tests\:testcafe\:debug tests\:autocomplete \
features\:owner

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

restart:
	@$(MAKE) -s down
	@$(MAKE) -s up

clean: | up
	$(call message,$(PROJECT_NAME): Removing vendor and web directories)
	$(call docker-root, php rm -rf vendor)
	$(call docker-root, php rm -rf web)
	$(call message,$(PROJECT_NAME): Removing node modules)
	docker-compose run --rm --user=0:0 node sh -c "rm -rf node_modules"
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

prepare:
    # Prepare composer dependencies.
	$(call message,$(PROJECT_NAME): Installing/updating composer dependencies)
	docker-compose run php composer install --no-suggest

	$(call message,$(PROJECT_NAME): Installing dependencies for React.js application)
	docker-compose run --rm node yarn install

    # Prepare public files folder.
	$(call message,$(PROJECT_NAME): Preparing public files directory)
	$(call docker-wodby, php mkdir -p web/sites/default/files)
	$(call docker-root, php chown -R www-data: web/sites/default/files)

    # Prepare settings.php file.
	$(call message,$(PROJECT_NAME): Making settings.php writable)
	$(call docker-wodby, php chmod 666 web/sites/default/settings.php)

	# Prepare settings.local.php file.
	@if [ $(ENVIRONMENT) = "development" ]; then \
		$(call docker-wodby, php chmod +w web/sites/default); \
		$(call docker-wodby, php cp web/sites/example.settings.local.php web/sites/default/settings.local.php); \
		$(call docker-wodby, php sed -i \"/settings.local.php';/s/# //g\" web/sites/default/settings.php); \
    fi

    # Prepare git hooks.
	$(call message,$(PROJECT_NAME): Setting up git hooks)
	ln -sf $(shell pwd)/.git-hooks/* $(shell pwd)/.git/hooks

install: | prepare up
	# Install Drupal using Falcon profile.
	$(call message,$(PROJECT_NAME): Installing Drupal)
	$(call docker-www-data, php drush -r web site-install falcon \
		--db-url=mysql://$(DB_USER):$(DB_PASSWORD)@$(DB_HOST)/$(DB_NAME) --site-name=$(PROJECT_NAME) --account-pass=admin \
		install_configure_form.enable_update_status_module=NULL --yes)

	# Enable dev modules.
	$(call message,$(PROJECT_NAME): Enabling development modules)
	@if [ $(ENVIRONMENT) = "development" ]; then \
		$(MAKE) -s drush en $(DEVELOPMENT_MODULES); \
	fi

	$(call message,Congratulations! You installed $(PROJECT_NAME)!)

code\:check:
    # PHP coding standards check.
	$(call message,$(PROJECT_NAME): Checking PHP for compliance with Drupal coding standards...)
	docker run --rm \
		-v $(shell pwd)/falcon/modules:/app/modules $(DOCKER_PHPCS) phpcs \
		-s --colors --warning-severity=0 --standard=Drupal,DrupalPractice .
    # Javascript coding standards check.
	$(call message,$(PROJECT_NAME): Checking Javascript for compliance with Drupal coding standards...)
	docker run --rm \
		-v $(shell pwd)/falcon/modules:/eslint/modules \
		-v $(shell pwd)/falcon/.eslintrc.json:/eslint/.eslintrc.json \
		$(DOCKER_ESLINT) .
	$(call message,$(PROJECT_NAME): Checking React.js code for compliance with coding standards)
	docker-compose run -T --rm node yarn --silent eslint

code\:fix:
	$(call message,$(PROJECT_NAME): Auto-fixing coding style issues...)
	docker run --rm \
		-v $(shell pwd)/falcon/modules:/app/modules $(DOCKER_PHPCS) phpcbf \
		-s --colors --warning-severity=0 --standard=Drupal,DrupalPractice .
	docker run --rm \
		-v $(shell pwd)/falcon/modules:/eslint/modules \
		-v $(shell pwd)/falcon/.eslintrc.json:/eslint/.eslintrc.json \
		$(DOCKER_ESLINT) --fix .
	$(call message,$(PROJECT_NAME): Auto-fixing React.js code issues)
	docker-compose run -T --rm node yarn --silent eslint --fix

yarn:
	$(call message,$(PROJECT_NAME): Running Yarn)
	$(eval ARGS := $(filter-out $@,$(MAKECMDGOALS)))
	docker-compose run --rm node yarn $(ARGS)

logs:
	$(call message,$(PROJECT_NAME): Streaming the Next.js application logs)
	docker-compose logs -f node

######################
# Testing operations #
######################

# MAKE can't properly forward options starting with two dashes so we
# introduce a new variable TESTMETA which corresponds to --test-meta option.
ifdef TESTMETA
  TESTMETA_OPTION=--test-meta $(TESTMETA)
else
  TESTMETA_OPTION=
endif

tests\:prepare:
	$(call message,$(PROJECT_NAME): Preparing Codeception framework for testing...)
	docker-compose run --rm codecept build

	$(call message,$(PROJECT_NAME): Installing test dependencies for end-to-end tests)
	docker-compose run --rm -T node sh -c "cd /tests && yarn install"

tests\:codeception:
	$(call message,$(PROJECT_NAME): Run Codeception tests)
	$(eval ARGS := $(filter-out $@,$(MAKECMDGOALS)))
	docker-compose run --rm codecept run $(ARGS) --debug

tests\:codeception\:cli:
	$(call message,$(PROJECT_NAME): Open Codeception container CLI)
	docker-compose run --rm --entrypoint bash codecept

tests\:testcafe:
	$(call message,$(PROJECT_NAME): Running end-to-end tests...)
	rm -rf ./tests/end-to-end/results/*
	docker-compose run --rm -T testcafe '$(TESTCAFE_BROWSERS)' \
      --screenshots-on-fails --screenshots results -p '$${USERAGENT}/$${FIXTURE}-$${TEST}-$${RUN_ID}.png' \
      --assertion-timeout 5000 --quarantine-mode \
      -r spec,xunit:/results/xunit.xml --color $(TESTMETA_OPTION) tests
	$(call message,$(PROJECT_NAME): All tests passed!)

tests\:testcafe\:debug:
	$(call message,$(PROJECT_NAME):@eRunning end-to-end tests in debug mode...)
	docker-compose run --service-ports --rm testcafe remote \
      --assertion-timeout 5000 \
      --hostname=localhost  --debug-on-fail $(TESTMETA_OPTION) tests

tests\:autocomplete:
	$(call message,$(PROJECT_NAME): Copy Codeception code in .codecept folder to enable IDE autocomplete)
	rm -rf .codecept
	docker cp $(PROJECT_NAME)_codecept:/repo/ .codecept
	rm -rf .codecept/.git

# Change ownership of features directory.
# Usage:
# - make features:owner www-data
# - write features from Features UI
# - make features:owner wodby
features\:owner:
	$(eval OWNER := $(filter-out $@,$(MAKECMDGOALS)))
	$(call docker-root, php chown -R $(OWNER): web/profiles/contrib/falcon/modules/features)

# https://stackoverflow.com/a/6273809/1826109
%:
	@:

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
yellow = \033[38;5;3m
bold = \033[1m
reset = \033[0m
message = @echo "${yellow}${bold}${1}${reset}"

# Define 3 users with different permissions within the container.
# docker-www-data is applicable only for php container.
docker-www-data = docker-compose exec --user=82:82 $(firstword ${1}) time -f"%E" sh -c "$(filter-out $(firstword ${1}), ${1})"
docker-wodby = docker-compose exec $(firstword ${1}) time -f"%E" sh -c "$(filter-out $(firstword ${1}), ${1})"
docker-root = docker-compose exec --user=0:0 $(firstword ${1}) time -f"%E" sh -c "$(filter-out $(firstword ${1}), ${1})"

# Helper funciton to build new docker images based on repo state, push to ECR
# and update .deployment/aws-values.yaml with corresponding image tags for
# further processing by Helm.
define docker-tag-build-push
	$(eval NAME:=${1})
	$(eval FOLDER:=${2})
	$(eval REPOSITORY:=${3})
	$(eval BUILD_ARGS:=${4})
	$(eval TAG:=$(shell .circleci/split/splitsh-lite$(filter Darwin, $(shell uname)) --quiet --prefix=${FOLDER}))
	$(call message,Looking for docker image ${REPOSITORY}:${TAG})
	$(eval TAG_SEARCH_EXIT_CODE:=$(shell docker-compose run --rm awscli ecr describe-images --repository-name=${REPOSITORY} --image-ids=imageTag=${TAG} >/dev/null 2>/dev/null; echo $$?))
	@$(if $(filter-out 0, ${TAG_SEARCH_EXIT_CODE}), \
		$(call message,No image found. Building and pushing new image...) \
		&& \
		docker build -f ./.docker/${NAME}/Dockerfile . -t ${AWS_ECR_REGISTRY_PREFIX}/${REPOSITORY}:${TAG} ${BUILD_ARGS} \
		&& \
		docker push "${AWS_ECR_REGISTRY_PREFIX}/${REPOSITORY}:${TAG}" \
		, \
		$(call message,Image ${REPOSITORY}:${TAG} already exists. Reusing.) \
		)
	$(call message,Setting image tag in .deployment/aws-values.yaml file)
	docker run --rm -v $(shell pwd)/.deployment:/workdir mikefarah/yq yq write -i aws-values.yaml images.$(subst -,,${NAME}).tag ${TAG}
endef

default: up

pull:
	$(call message,$(PROJECT_NAME): Updating Docker images)
	docker-compose pull
	docker pull $(DOCKER_PHPCS)
	docker pull $(DOCKER_ESLINT)

up:
	$(call message,$(PROJECT_NAME): Build and run containers)
	docker-compose up -d --remove-orphans --scale codecept=0 --scale testcafe=0

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
	# Spin up mysql container early to give it sufficient time to launch before usage.
	docker-compose up -d mariadb

    # Prepare composer dependencies.
	$(call message,$(PROJECT_NAME): Installing/updating composer dependencies)
	docker-compose run --rm php composer install --no-suggest

	$(call message,$(PROJECT_NAME): Installing dependencies for React.js application)
	docker-compose run --rm node yarn install

    # Prepare public files folder.
	$(call message,$(PROJECT_NAME): Preparing public files directory)
	docker-compose run --rm php mkdir -p web/sites/default/files
	docker-compose run --rm -u root php chown -R www-data: web/sites/default/files

    # Prepare settings.php file.
	$(call message,$(PROJECT_NAME): Making settings.php writable)
	docker-compose run --rm php chmod 666 web/sites/default/settings.php

	# Prepare settings.local.php file.
	@if [ $(ENVIRONMENT) = "development" ]; then \
		docker-compose run --rm php chmod +w web/sites/default; \
		docker-compose run --rm php cp web/sites/example.settings.local.php web/sites/default/settings.local.php; \
		docker-compose run --rm php sed -i "/settings.local.php';/s/# //g" web/sites/default/settings.php; \
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

	# Generate XML sitemap.
	$(MAKE) -s drush simple-sitemap-generate

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

###############################################
############### AWS COMMANDS ##################
###############################################
docker\:login:
	$(call message,Logging Docker daemon into AWS ECR registry...)
	@eval $(shell docker-compose run -T --rm awscli ecr get-login --no-include-email)

docker\:tag-build-push:
	$(call message,$(PROJECT_NAME): Make some files read only for security purposes)
	find falcon/web/sites/default \
		-maxdepth 1 -type f -exec chmod a=r {} \;
	$(call docker-tag-build-push,nginx,falcon,falcon-nginx,--build-arg NGINX_TAG=$(NGINX_TAG))
	$(call docker-tag-build-push,php,falcon,falcon-php,--build-arg PHP_TAG=$(PHP_TAG))
	$(call docker-tag-build-push,node,falconjs,falcon-node,--build-arg NODE_TAG=$(NODE_TAG))
	$(call message,$(PROJECT_NAME): Restore file permissions)
	find falcon/web/sites/default \
		-maxdepth 1 -type f -exec chmod 644 {} \;
	$(call message,$(PROJECT_NAME): Done!)


####################################################
################ Testing operations ################
####################################################
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
	docker-compose run --rm node yarn install

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

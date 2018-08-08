.PHONY: default pull up stop down exec-www-data exec exec-root drush \
prepare prepare-composer prepare-files \
install

# Create local environment files.
$(shell cp -n \.\/\.docker\/docker-compose\.override\.default\.yml \.\/\.docker\/docker-compose\.override\.yml)
$(shell cp -n \.env\.default \.env)
include .env

# Define function to highlight messages.
# @see https://gist.github.com/leesei/136b522eb9bb96ba45bd
cyan = \033[38;5;6m
bold = \033[1m
reset = \033[0m
message = @echo "${cyan}${bold}${1}${reset}"

# Define 3 users with different permissions within the container.
# docker-www-data is applicable only for php container.
docker-www-data = docker-compose exec --user=82:82 $(firstword ${1}) time sh -c "$(filter-out $(firstword ${1}), ${1})"
docker-wodby = docker-compose exec $(firstword ${1}) time sh -c "$(filter-out $(firstword ${1}), ${1})"
docker-root = docker-compose exec --user=0:0 $(firstword ${1}) time sh -c "$(filter-out $(firstword ${1}), ${1})"

default: up

pull:
	$(call message,$(PROJECT_NAME): Updating Docker images)
	docker-compose pull

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
	$(call message,PHP: Removing vendor and web directories)
	$(call docker-root, php rm -rf vendor)
	$(call docker-root, php rm -rf web)
	$(MAKE) -s down

exec:
# Remove the first argument from the list of make commands.
	$(eval ARGS := $(filter-out $@,$(MAKECMDGOALS)))
	$(eval TARGET := $(firstword $(ARGS)))
	docker-compose exec --user=82:82 $(TARGET) sh

exec-wodby:
# Remove the first argument from the list of make commands.
	$(eval ARGS := $(filter-out $@,$(MAKECMDGOALS)))
	$(eval TARGET := $(firstword $(ARGS)))
	docker-compose exec $(TARGET) sh

exec-root:
# Remove the first argument from the list of make commands.
	$(eval ARGS := $(filter-out $@,$(MAKECMDGOALS)))
	$(eval TARGET := $(firstword $(ARGS)))
	docker-compose exec --user=0:0 $(TARGET) sh

drush:
# Remove the first argument from the list of make commands.
	$(eval COMMAND_ARGS := $(filter-out $@,$(MAKECMDGOALS)))
	$(call message,Executing \"drush -r /var/www/html/web $(COMMAND_ARGS) --yes\")
	$(call docker-www-data, php drush -r /var/www/html/web $(COMMAND_ARGS) --yes)

prepare: | up prepare-composer prepare-files prepare-settings

prepare-composer:
	$(call message,PHP: Installing/updating composer dependencies)
	-$(call docker-wodby, php composer install --no-suggest)

prepare-files:
	$(call message,PHP: Preparing public files directory)
	$(call docker-wodby, php mkdir -p web/sites/default/files)
	$(call docker-root, php chown -R www-data: web/sites/default/files)

prepare-settings:
	$(call message,PHP: Making settings.php writable)
	$(call docker-wodby, php chmod 666 web/sites/default/settings.php)

install: | prepare
	$(call message,PHP: Installing site)
	$(call docker-www-data, php drush -r /var/www/html/web si falcon --db-url=mysql://$(DB_USER):$(DB_PASSWORD)@$(DB_HOST)/$(DB_NAME) --account-pass=admin --yes)

# https://stackoverflow.com/a/6273809/1826109
%:
	@:

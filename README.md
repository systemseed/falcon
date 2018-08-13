[![CircleCI](https://circleci.com/gh/systemseed/falcon/tree/master.svg?style=svg)](https://circleci.com/gh/systemseed/falcon/tree/master)

# Documentation
Latest documentation is available at https://falcon-platform.readthedocs.io/

# Installation

### Install Docker
You need to install Docker and Docker-compose.
See https://docs.docker.com/install/ for details.

### Make local environment
Open command line terminal, navigate to project root and run `make install`.
If you want to adjust local environment settings then first run `make stop`
and review the `.env` and `.docker/docker-compose.override.yml` files.
After that run `make install`.

Then you can stop your work by running `make stop` and continue by running
`make up`. To remove all the containers you can run `make down`.

### Update local environment
If there were some changes in the upstream that render your local environment
settings out of date - you need to update the `.env` and
`docker/docker-compose.override.yml` files. There are 2 ways to do it - just
remove them and run any `make` command or review them manually and compare with
the sources:
* `.env` file comes from `.env.default`
* `.docker/docker-compose.override.yml` comes from `.docker/docker-compose.override.default.yml`.

### Access the site
Go to http://falcon.docker.localhost

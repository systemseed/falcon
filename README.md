[![CircleCI](https://circleci.com/gh/systemseed/falcon/tree/master.svg?style=svg)](https://circleci.com/gh/systemseed/falcon/tree/master)
[![semantic-release](https://img.shields.io/badge/%20%20%F0%9F%93%A6%F0%9F%9A%80-semantic--release-e10079.svg)](https://github.com/semantic-release/semantic-release)

This is a development repository for Falcon backend (powered by Drupal) and frontend (powered by Next.js).
For quick demo or quick start of using Falcon you can use our one-command-install project [Falcon Starter Kit](https://github.com/systemseed/falcon-starter-kit).

# Documentation

The usage documentation is available at https://falcon-platform.readthedocs.io/

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
Backend: `http://admin.docker.localhost` (credentials: `admin` / `admin`)
Demo frontend (work in progress): `http://frontend.docker.localhost`

### Production-ready installations

 🛠 **Work in progress!**

We are working on a scaffolding tool for production-ready project installations.

# Repository

This is a monorepo repository that contains:

- **Falcon backend** in `falcon` folder. Drupal-based distribution.
- **Falcon.js** in `falconjs/packages/falcon` folder. A complementary React
library for integrating Next.js frontend apps with Falcon backend.
- **Demo frontend** in `falconjs/templates/default` folder. Frontend boilerplate with
demo of Falcon features.

# Releases

New code is **automatically** released after merge in master.  

Latest releases are available here:

Falcon backend: https://packagist.org/packages/systemseed/falcon
Falcon.js: https://www.npmjs.com/package/@systemseed/falcon


## Commit messages

In order to make automated releases work, commit
messages should follow default `semantic-release` message format: https://semantic-release.gitbook.io/semantic-release/#commit-message-format

We recommend to use a tool like [commitizen](https://github.com/commitizen/cz-cli) or a plugin like [Git Commit Template](https://plugins.jetbrains.com/plugin/9861-git-commit-template) for preparing informative commit messages.

### Examples

**Commit message that triggers a major release, i.e. 1.4.3 → 2.0.0**

```
feat(backend): change all API endpoints to use new format.

BREAKING CHANGE: All API endpoints now use new output format.
```
Note that there is a comment that starts with **BREAKING CHANGE:** ! The rest of commit message does not matter.


**Commit message that triggers a MINOR release, i.e. 1.4.3 → 1.5.0**

```
feat(frontend): add routing support.
```

**Commit message that triggers a PATCH release, i.e. 1.4.3 → 1.4.4**

```
fix(frontend): timeout issue in routing package on 404 pages.
```

**Commit message that does not trigger a release**

```
docs(frontend): add routing package documentation.
```

# Security

If you found a security issue, please immediately contact us at <a href="mailto:info@systemseed.com">info@systemseed.com</a>.


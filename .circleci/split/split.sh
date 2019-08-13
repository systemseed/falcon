#!/usr/bin/env bash
# Inspired by Laravel monorepo setup. See:
# - https://github.com/laravel/framework
# - https://github.com/splitsh/lite

set -e
set -x

function split()
{
    SHA1=`.circleci/split/splitsh-lite --prefix=$1`
    git push $2 "$SHA1:master" -f
}

function remote()
{
    git remote add $1 $2 || true
}

git pull origin releases

remote falcon git@github.com:systemseed/falcon-backend.git
remote falconjs git@github.com:systemseed/falcon.js.git

split 'falcon' falcon
split 'falconjs/packages/falcon' falconjs

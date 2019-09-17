#!/usr/bin/env bash

# Prints remote URLs to a given .env file.
# Usage:
# - ./urls_to_file.sh falcon stage /my/.env-file

set -e

CHART=$1
BRANCH=$2
ENV_FILE=$3

NAMESPACE="test"

# Getting hosts from ingress.
BACKEND_HOST=$(kubectl -n $NAMESPACE get ingress -l release=falcon-$BRANCH,component=php -o jsonpath={.items[0].spec.rules[0].host})
FRONTEND_HOST=$(kubectl -n $NAMESPACE get ingress -l release=falcon-$BRANCH,component=node -o jsonpath={.items[0].spec.rules[0].host})

# Printing URLs in the same format as before.
echo "FRONTEND_URL=https://$FRONTEND_HOST/" >> $ENV_FILE
echo "BACKEND_URL=https://$BACKEND_HOST/" >> $ENV_FILE
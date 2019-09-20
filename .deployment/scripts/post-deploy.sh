#!/usr/bin/env bash

# This is post-deployment script for Drupal backend. Usually executed from CI.
# Usage:
# - ./post-deploy.sh drupal node master

set -e

CHART=$1
COMPONENT=$2
BRANCH=$3
PASSWORD=$4

NAMESPACE="test"

# Waiting for the deployment to complete.
kubectl -n $NAMESPACE rollout status -w deployment/falcon-$BRANCH-$CHART-$COMPONENT

# Getting recent Drupal pod ID from deployed release.
POD_ID=$(kubectl -n $NAMESPACE get pods -l "release=falcon-$BRANCH,component=$COMPONENT"  -o jsonpath={.items[*].metadata.name} --sort-by=.metadata.creationTimestamp | awk '{print $NF}')
echo "Selected POD ID for executing post-deployment tasks: $POD_ID"

echo "Install drupal"
time kubectl -n $NAMESPACE exec -it $POD_ID -c php -- bash -c "drush -r web site-install falcon	--site-name=Falcon --account-pass=$PASSWORD install_configure_form.enable_update_status_module=NULL --yes; (exit $?)"
echo "Enabling development modules"
time kubectl -n $NAMESPACE exec -it $POD_ID -c php -- bash -c 'drush -r web en $DEVELOPMENT_MODULES --yes; (exit $?)'
echo "Generate sitemap"
time kubectl -n $NAMESPACE exec -it $POD_ID -c php -- bash -c 'drush -r web simple-sitemap-generate; (exit $?)'

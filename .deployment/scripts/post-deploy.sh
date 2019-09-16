#!/usr/bin/env bash

# This is post-deployment script for Drupal backend. Usually executed from CI.
# Usage:
# - ./post-deploy.sh falcon php master

set -e

CHART=$1
COMPONENT=$2
BRANCH=$3

# TODO: fetch actual namespace from configs, not from branch names!
[[ $BRANCH = "master" && $CHART != "falcon" ]] && NAMESPACE="prod" || NAMESPACE="test"

# Waiting for the deployment to complete.
kubectl -n $NAMESPACE rollout status -w deployment/$CHART-$BRANCH-$COMPONENT

# Getting recent Drupal pod ID from deployed release.
POD_ID=$(kubectl -n $NAMESPACE get pods -l "release=$CHART-$BRANCH,component=$COMPONENT"  -o jsonpath={.items[*].metadata.name} --sort-by=.metadata.creationTimestamp | awk '{print $NF}')
echo "Selected POD ID for executing post-deployment tasks: $POD_ID"
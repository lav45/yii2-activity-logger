#!/usr/bin/env bash

set -e

docker build --pull -f Dockerfile -t yii2-activity-logger-dev .
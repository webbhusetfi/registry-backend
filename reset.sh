#!/bin/bash

php bin/console doctrine:cache:clear-metadata
php bin/console doctrine:cache:clear-query
php bin/console doctrine:cache:clear-result

php bin/console cache:clear
php bin/console cache:warmup

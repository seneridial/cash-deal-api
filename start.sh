#!/bin/bash
php artisan config:clear
php artisan cache:clear
php artisan storage:link
php artisan migrate --force
php artisan db:seed --force
apache2-foreground
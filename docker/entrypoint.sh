#!/bin/sh
set -e

php -r "set_time_limit(60);for(;;){if(@fsockopen('mysql',3306)){break;}echo \"Waiting for MySQL\n\";sleep(1);}"

php bin/console cache:clear --no-warmup
php bin/console cache:warmup
php bin/console d:s:u -f

chown -R www-data var

exec "$@"
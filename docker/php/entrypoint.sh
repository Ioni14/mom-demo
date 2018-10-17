#!/bin/bash
set -e

cp /etc/php/7.2/fpm/php.ini /etc/php/7.2/fpm/php.ini.new

if [ ${CONTAINER_ENV} = prod ]; then
    # active apcu and memcached
    rm -f /etc/php/7.2/fpm/conf.d/25-memcached.ini
    ln -s /etc/php/7.2/mods-available/memcached.ini /etc/php/7.2/fpm/conf.d/25-memcached.ini
    rm -f /etc/php/7.2/fpm/conf.d/20-apcu.ini
    ln -s /etc/php/7.2/mods-available/apcu.ini /etc/php/7.2/fpm/conf.d/20-apcu.ini
else
    # active xdebug
    echo "zend_extension="`find /usr/lib/php -name 'xdebug.so' 2> /dev/null`"" | tee /etc/php/7.2/mods-available/xdebug.ini
    # xdebug for fpm
    rm -f /etc/php/7.2/fpm/conf.d/20-xdebug.ini
    ln -s /etc/php/7.2/mods-available/xdebug.ini /etc/php/7.2/fpm/conf.d/20-xdebug.ini
    sed -r -i "s/xdebug\.remote_host ?\=.*$/xdebug.remote_host=$(ip route|awk '/default/ { print $3 }')/" /etc/php/7.2/fpm/php.ini.new
    # xdebug for cli
    rm -f /etc/php/7.2/cli/conf.d/20-xdebug.ini
    ln -s /etc/php/7.2/mods-available/xdebug.ini /etc/php/7.2/cli/conf.d/20-xdebug.ini
fi;

sed -r -i "s/SMTP ?\=.*$/SMTP=${MAIL_HOST}/" /etc/php/7.2/fpm/php.ini.new
sed -r -i "s/smtp_port ?\=.*$/smtp_port=${MAIL_PORT}/" /etc/php/7.2/fpm/php.ini.new

cp -f /etc/php/7.2/fpm/php.ini.new /etc/php/7.2/fpm/php.ini
rm -f /etc/php/7.2/fpm/php.ini.new

exec "$@"

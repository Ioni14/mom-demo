FROM ubuntu:18.04

LABEL maintainer="Thomas Talbot <thomas.talbot@zephyr-web.fr>"

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y locales \
    && locale-gen fr_FR.UTF-8

ENV LANG fr_FR.UTF-8
ENV LANGUAGE fr_FR:fr
ENV LC_ALL fr_FR.UTF-8

RUN apt-get update && apt-get dist-upgrade -y && apt-get autoremove -y && apt-get install --no-install-recommends -y \
        wget \
        curl \
        zip \
        unzip \
        git \
        make \
        iproute2 \
        acl \
        software-properties-common \
        openssl \
    && add-apt-repository -y ppa:ondrej/php \
    && apt-get update && apt-get install --no-install-recommends -y \
        php7.2-cli \
        php7.2-common \
        php7.2-fpm \
        php7.2-json \
        php7.2-intl \
        php7.2-pgsql \
        php7.2-xml \
        php7.2-zip \
        php7.2-mbstring \
        php7.2-curl \
        php7.2-bcmath \
        php7.2-tidy \
        php7.2-apcu \
        php7.2-amqp \
        php-pear \
        php7.2-dev \
    && pecl install xdebug-2.6.0 \
    && apt-get remove -y --purge software-properties-common make php-pear php7.2-dev \
    && apt-get autoremove -y \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
    && mkdir -p /run/php \
    && rm -f /etc/php/7.2/fpm/conf.d/25-memcached.ini /etc/php/7.2/fpm/conf.d/20-apcu.ini \
    ;

# For running wkhtmltopdf
#RUN apt-get update && apt-get install --no-install-recommends -y \
#        libfontconfig1 \
#        libxrender1 \
#        libxext6 \
#        libssl1.0-dev \
#    && apt-get autoremove -y \
#    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
#    ;

COPY install-composer.sh /tmp/install-composer.sh
RUN sh /tmp/install-composer.sh \
    && mv composer.phar /usr/local/bin/composer \
    && rm -f /tmp/install-composer.sh \
    ;

#RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
#    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/amd64/$version \
#    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp \
#    && mv /tmp/blackfire-*.so $(php -r "echo ini_get('extension_dir');")/blackfire.so \
#    && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707\n" > /etc/php/7.2/fpm/conf.d/blackfire.ini \
#    && rm -rf /tmp/* \
#    ;

COPY entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

WORKDIR /var/www/html

EXPOSE 9000
ENTRYPOINT ["entrypoint"]
CMD ["php-fpm7.2", "-F"]

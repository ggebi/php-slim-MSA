FROM php:7.2-apache

RUN \
    apt-get update && \
    # install dependencies
     apt-get install -y \
      git-core \
      zip \
      unzip \
    && \
    # install PHP extensions
    docker-php-ext-install \
      pdo_mysql

# enable apache module
RUN \
    # modify apache log format
    sed -i 's/%h/%a/' /etc/apache2/apache2.conf \
    && \
    # modify apache header information
    sed -i 's/ServerTokens OS/ServerTokens Prod/' /etc/apache2/conf-enabled/security.conf \
    && \
    # allow internal proxy ip
    echo 'RemoteIPHeader X-Forwarded-For\nRemoteIPInternalProxy 10.42.0.0/16' > /etc/apache2/mods-available/remoteip.conf && \
    # php default timezone
    echo 'date.timezone = Asia/Seoul' > /usr/local/etc/php/conf.d/timezone.ini \
    && \
    # php x-powered-by header delete.
    echo 'expose_php = Off' > /usr/local/etc/php/conf.d/php.ini \
    && \
    a2enmod \
      rewrite \
      remoteip

# install composer
RUN curl -s -f -L -o /tmp/installer.php https://getcomposer.org/installer \
    && php /tmp/installer.php --no-ansi --install-dir=/usr/bin --filename=composer \
    && composer --ansi --version --no-interaction

# install composer dependencies
COPY composer.json /var/www
RUN cd /var/www && /usr/bin/composer install --no-plugins --no-scripts --prefer-dist --no-dev -o && rm composer.lock
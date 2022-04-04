FROM registry.bankmedia.co.kr/yesfile/backend-api-baseimage:7.2-apache-0.4

# copy files
COPY docker-php-entrypoint /usr/local/bin/
COPY src/ /var/www/src/
COPY models/ /var/www/models/
COPY html/ /var/www/html/
COPY third-party/ /var/www/third-party/
COPY composer.json /var/www/

# optimize autoloader
WORKDIR /var/www
RUN composer dump-autoload -o
FROM registry.bankmedia.co.kr/yesf/backend-api-baseimage:7.2-apache-0.4

# copy files
COPY docker-php-entrypoint /usr/local/bin/
COPY src/ /var/www/src/
COPY models/ /var/www/models/
COPY html/ /var/www/html/
COPY third-party/ /var/www/third-party/
COPY tests/ /var/www/tests/

COPY composer.json /var/www/

WORKDIR /var/www
# RUN composer update -o
RUN composer dump-autoload --optimize
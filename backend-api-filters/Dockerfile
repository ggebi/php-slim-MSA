FROM registry.bankmedia.co.kr/yesfile/backend-api-baseimage:7.2-apache-0.4

ENV GeoFile=GeoLite2-Country_20180807

# copy files
COPY docker-php-entrypoint /usr/local/bin/
COPY src/ /var/www/src/
COPY models/ /var/www/models/
COPY html/ /var/www/html/
COPY third-party/ /var/www/third-party/
COPY composer.json /var/www/
COPY ${GeoFile}.tar.gz /var/www

WORKDIR /var/www

RUN tar -xzf ${GeoFile}.tar.gz --exclude=*.txt \
    && \
    mv ${GeoFile}/*.mmdb /var/www/ \
    && \
    rmdir ${GeoFile} \
    && \
    rm ${GeoFile}.tar.gz

RUN composer update -o
FROM docker.elastic.co/logstash/logstash:6.2.1

ADD conf/GeoLite2-City_20170606.mmdb /config-dir/
ADD conf/pipelines.yml /usr/share/logstash/config/
ADD conf/logstash.conf /usr/share/logstash/pipeline/
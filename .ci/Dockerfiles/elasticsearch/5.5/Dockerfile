FROM docker.elastic.co/elasticsearch/elasticsearch:5.5.2

USER root
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

USER elasticsearch

ENTRYPOINT ["/entrypoint.sh"]

FROM selenium/node-firefox:2.53.1-beryllium

USER root

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 4444

ENTRYPOINT ["/entrypoint.sh"]

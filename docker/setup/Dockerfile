FROM debian:stretch-slim

RUN useradd docker --shell /bin/bash --create-home \
  && usermod --append --groups sudo docker \
  && echo 'ALL ALL = (ALL) NOPASSWD: ALL' >> /etc/sudoers \
  && echo 'docker:secret' | chpasswd


COPY setup-pim /srv
RUN chmod +x /srv/setup-pim
RUN chown docker:docker /srv/setup-pim

USER docker

ENTRYPOINT ["/srv/setup-pim"]

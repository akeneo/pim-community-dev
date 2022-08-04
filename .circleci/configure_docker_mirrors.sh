#!/bin/bash -eu

# see https://cloud.google.com/container-registry/docs/pulling-cached-images#configure
echo '{"registry-mirrors": ["https://mirror.gcr.io"]}' >> /etc/docker/daemon.json

service docker restart

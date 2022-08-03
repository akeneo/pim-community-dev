#!/bin/bash

# see https://cloud.google.com/container-registry/docs/pulling-cached-images#configure
echo 'DOCKER_OPTS="${DOCKER_OPTS} --registry-mirror=https://mirror.gcr.io"' >> /etc/default/docker

service docker restart

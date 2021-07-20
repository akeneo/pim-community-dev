#!/usr/bin/env bash

set -e

BINDIR=$(dirname $(readlink -f $0))

# Use this script to compile grpc & protobuf extensions when needed (compatibility issue, security upgrade, ...)

DOCKER_BUILDKIT=1 docker build --progress=plain --target=compile-extensions -t akeneo/pim-dev/php:7.4-extensions ${BINDIR}/../. && \
EXTENSION_DIR=`docker run --rm akeneo/pim-dev/php:7.4-extensions sh -c "php -r 'echo ini_get(\"extension_dir\");'"` && \
clear && \

rm ${BINDIR}/build/grpc.tar.gz || true && \
rm ${BINDIR}/build/protobuf.tar.gz || true && \

echo "Copying grpc.so extension" && \
docker run --rm --entrypoint cat akeneo/pim-dev/php:7.4-extensions `echo "$EXTENSION_DIR"`/grpc.so > ${BINDIR}/build/grpc.so && \
echo "Compress grpc.so" && \
tar czf ${BINDIR}/build/grpc.tar.gz -C ${BINDIR}/build grpc.so  && \
rm ${BINDIR}/build/grpc.so || true && \

echo "Copying protobuf.so extension" && \
docker run --rm --entrypoint cat akeneo/pim-dev/php:7.4-extensions `echo "$EXTENSION_DIR"`/protobuf.so > ${BINDIR}/build/protobuf.so && \
echo "Compress protobuf.so" && \
tar czf ${BINDIR}/build/protobuf.tar.gz -C ${BINDIR}/build protobuf.so && \
rm ${BINDIR}/build/protobuf.so || true && \

echo "Copy grpc and protobuf to grth folder"
cp ${BINDIR}/build/grpc.tar.gz ${BINDIR}/../grth/docker/build/grpc.tar.gz
cp ${BINDIR}/build/grpc.ini ${BINDIR}/../grth/docker/build/grpc.ini
cp ${BINDIR}/build/protobuf.tar.gz ${BINDIR}/../grth/docker/build/protobuf.tar.gz
cp ${BINDIR}/build/protobuf.ini ${BINDIR}/../grth/docker/build/protobuf.ini

echo "Copy grpc and protobuf to tria folder"
cp ${BINDIR}/build/grpc.tar.gz ${BINDIR}/../tria/docker/build/grpc.tar.gz
cp ${BINDIR}/build/grpc.ini ${BINDIR}/../tria/docker/build/grpc.ini
cp ${BINDIR}/build/protobuf.tar.gz ${BINDIR}/../tria/docker/build/protobuf.tar.gz
cp ${BINDIR}/build/protobuf.ini ${BINDIR}/../tria/docker/build/protobuf.ini

echo "Extension updated and retrieved locally, ready to be versionned."

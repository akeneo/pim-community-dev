# Using a local object storage

## Context

In order to locally test the proper utilization of the Flysystem abstraction,
it's now possible to use a local object storage container, similar in
operation and behavior to Google Cloud Storage or Amazon S3.

For that, we use Minio (https://min.io/), which provides a Docker image
and S3 compatibility.

## Usage

### Linux

The container is part of the docker compose file and will be started up when doing a `make up` or `docker-compose up`.

The object storage is configured by default to store files in `docker/object_storage/`.

It's also possible to browse the storage through [Min.io UI](http://localhost:9090/).

## Mac OS

Everything is explained on the [Min.io website](https://min.io/download#/macos).

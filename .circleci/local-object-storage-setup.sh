#!/usr/bin/env bash

SCRIPT_DIR=$(dirname $0)


cat >> $SCRIPT_DIR/../app/config/parameters_test.yml <<OBJECT_STORAGE_CONF

services:
    acme.s3_client:
        class: Aws\S3\S3Client
        factory_class: Aws\S3\S3Client
        factory_method: factory
        arguments:
            -
                endpoint: 'http://object-storage:9000/asset/'
                version: 'latest'
                region: 'us-west-1'
                use_path_style_endpoint: true
                credentials:
                    key: "AKENEO_OBJECT_STORAGE_ACCESS_KEY"
                    secret: "AKENEO_OBJECT_STORAGE_SECRET_KEY"

oneup_flysystem:
    adapters:
        catalog_storage_adapter:
            awss3v3:
                client: acme.s3_client
                bucket: catalog
        jobs_storage_adapter:
            awss3v3:
                client: acme.s3_client
                bucket: jobs
        archivist_adapter:
            awss3v3:
                client: acme.s3_client
                bucket: archive

OBJECT_STORAGE_CONF


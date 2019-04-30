# Using a local object storage

## Context

In order to locally test the proper utilization of the Flysystem abstraction,
it's now possible to use a local object storage container, similar in
operation and behavior to Google Cloud Storage or Amazon S3.

For that, we use Minio (https://min.io/), which provides a Docker image
and S3 compatibility.

## Configuration

To use the provided Minio object storage, the following configuration must
be done on the PIM, for example inside your `parameters.yml` file, in order
to avoid changing the default config files or creating a dedicated bundle:

```yaml
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
        asset_storage_adapter:
            awss3v3:
                client: acme.s3_client
                bucket: asset
        catalog_storage_adapter:
            awss3v3:
                client: acme.s3_client
                bucket: catalog
```

## Usage
The container is part of the docker compose file and will be started up when doing a `make up` or
`docker-compose up`.

The object storage is configured by default to store files in `docker/object_storage/`.

It's also possible to browse the storage through Min.io UI, by mapping a local port to the port 9000
of the container.

You can configure that inside your `docker-compose.override.yml`. The following configuration will allow
you to access Min.io UI by point your browser to to http://localhost:9090/. The credentials are defined
in the configuration above.
```yaml
    object-storage:
      ports:
         - '9090:9000'

```

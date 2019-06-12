# Cloud SaaS Migration from 3.1 to 3.2

The goal is to relocate all files that are accessible by multiple environments (fronts or daemons) into an object storage properly configured. This should allow us to remove the brittle NFS \o/. 

## Configuration

This documentation will go through the setup of a google storage bucket, but it could work with other providers. Please note that we'll *use only one bucket for all different PIM storages* (catalog, assets, archivist etc...). Those different storages will use a different directory inside the bucket.

1. Install the proper FlySystem adapter

`composer require "superbalist/flysystem-google-storage":"^7.2"`

2. Create the bucket

Ask for the cloud team to create your [bucket(s)](https://console.cloud.google.com/storage/browser). 
Normally, they should use 3 environment variables corresponding to your project and bucket configurations: 
- GOOGLE_CLOUD_PROJECT
- SRNT_GOOGLE_APPLICATION_CREDENTIALS
- SRNT_GOOGLE_BUCKET_NAME

3. Configure the bucket inside the PIM


```yaml
# app/config/config.yml
services:

    Google\Cloud\Storage\StorageClient:
        arguments:
            - projectId: '%env(GOOGLE_CLOUD_PROJECT)%'
              keyFilePath: '%env(SRNT_GOOGLE_APPLICATION_CREDENTIALS)%'

    Google\Cloud\Storage\Bucket:
        class: 'Google\Cloud\Storage\Bucket'
        factory: 'Google\Cloud\Storage\StorageClient:bucket'
        arguments:
            - '%env(SRNT_GOOGLE_BUCKET_NAME)%'
```

4. Configure FlySystem adapters

```yaml
# app/config/config.yml
oneup_flysystem:
    adapters:
        catalog_storage_adapter:
            googlecloudstorage:
                client: 'Google\Cloud\Storage\StorageClient'
                bucket: 'Google\Cloud\Storage\Bucket'
                prefix: 'catalog_storage'
        jobs_storage_adapter:
            googlecloudstorage:
                client: 'Google\Cloud\Storage\StorageClient'
                bucket: 'Google\Cloud\Storage\Bucket'
                prefix: 'jobs_storage'
        asset_storage_adapter:
            googlecloudstorage:
                client: 'Google\Cloud\Storage\StorageClient'
                bucket: 'Google\Cloud\Storage\Bucket'
                prefix: 'asset_storage'
        archivist_adapter:
            googlecloudstorage:
                client: 'Google\Cloud\Storage\StorageClient'
                bucket: 'Google\Cloud\Storage\Bucket'
                prefix: 'archivist'
        logs_adapter:
            googlecloudstorage:
                client: 'Google\Cloud\Storage\StorageClient'
                bucket: 'Google\Cloud\Storage\Bucket'
                prefix: 'log'
```

5. Configure authentication logs to use FlySystem

Before:

```yaml
# app/config/config_prod.yml
monolog:
    handlers:
        #...
        authentication:
            type: rotating_file
            path: '%kernel.logs_dir%/authentication.log'
            level: error
            max_files: 10
            channels: ['security', 'request']

```

In 3.2:

```yaml
# app/config/config_prod.yml
monolog:
    handlers:
        #...
        authentication:
            type: service
            id: 'Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log\FlySystemLogHandler'
            channels: ['security', 'request']
```

## Data migration

For this migration, we'll use PHP scripts as well as _gsutils cp_. The _gsutils cp_ command allows to copy data between your local file system and the an object storage. It's very fast, robust and multi-threaded. For your information, it took us ~50 minutes to upload ~500K medias (~90GB data). Please, take a look at the official documentation to see how to [install it](https://cloud.google.com/storage/docs/gsutil_install).

The following files have to be migrated:
- import/export logs (that are downloadable from the UI)

`php upgrades/schema/archived_logs_local_filesystem_to_object_storage.php`

- import/export archives (that are downloadable from the UI)

`php upgrades/schema/archived_files_local_filesystem_to_object_storage.php`

- product medias

`gsutil -m cp -r app/file_storage/catalog/ gs://${SRNT_GOOGLE_BUCKET_NAME}/catalog_storage`

- assets

`gsutil -m cp -r app/file_storage/asset/ gs://${SRNT_GOOGLE_BUCKET_NAME}/asset_storage`


Please note that the scripts and commands DO NOT remove source files. In case of error, the scripts will list files that couldn't have been relocated (for instance if a file is missing on the filesystem).

If you want to remove sources files, please use:

```bash
rm app/archive/import/* -rf
rm app/archive/export/* -rf
rm app/file_storage/catalog/* -rf
rm app/file_storage/asset/* -rf
```

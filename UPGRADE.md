# UPGRADE FROM 3.1 TO 3.2

Use this documentation to migrate projects based on the Enterprise Edition.

## Disclaimer

> When starting your upgrade, make sure:
>  - you created your project from the standard distribution.
>  - you performed a backup of your database before proceeding to the migration. You can use tools like [mysqldump](https://dev.mysql.com/doc/refman/5.7/en/mysqldump.html).
>  - you performed a backup of your indices before proceeding to the migration. You can use Elastisearch API [_snapshot](https://www.elastic.co/guide/en/elasticsearch/reference/6.5/modules-snapshots.html).
>  - you performed a backup of your codebase if you don't use a VCS (Version Control System).

## Requirements

Make sure that you're using Akeneo PIM Enterprise Edition v3.1 on the latest patch. You can check this information at the bottom of the dashboard.

Please, see the complete [list of requirements](https://docs.akeneo.com/3.2/install_pim/manual/system_requirements/system_requirements.html) for PIM v3.2.

Please provide a server with the following requirements before proceeding to the PIM 3.2 migration. To install those requirements, you can follow the official documentations or our installation documentation on [Debian 9](https://docs.akeneo.com/3.2/install_pim/manual/system_requirements/manual_system_installation_debian9.html) or [Ubuntu 16.04](https://docs.akeneo.com/3.2/install_pim/manual/system_requirements/system_install_ubuntu_1604.html).

## Migrate your standard project

1. Stop the job queue consumer daemon consumer

    Before starting the migration process, stop the job queue consumer daemon and start it again only when the migration process is finished.

    If you use `supervisor`, then stop your daemon as following:

    ```bash
    supervisorctl status
    # the command returns the following daemons
    # pim-queue-daemon:pim-queue-daemon_00 RUNNING    pid 4162, uptime 0:05:44

    supervisorctl stop pim-queue-daemon:pim-queue-daemon_00

    supervisorctl status
    # the daemon has been stopped
    # pim-queue-daemon:pim-queue-daemon_00 STOPPED    Jan 24 11:41 AM

    ```

    Otherwise, kill your daemon:

    ```bash
    pkill -f job-queue-consumer-daemon
    ```

2. Download the latest standard edition from the [Partner Portal](https://partners.akeneo.com/login) and extract it:

    ```bash
    tar -zxf pim-enterprise-standard.tar.gz
    cd pim-enterprise-standard/
    ```

3. Update the configuration files:

    The `$PIM_DIR` variable will contain the path to your current PIM installation:

    ```bash
    export PIM_DIR=/path/to/your/current/pim/installation
    ```

    We will copy the configuration file from the new standard edition version.
    You shouldn't have made a single change to them in your project, but if you did, don't forget to reapply your own changes to the files.

    Then apply the changes, from the standard edition directory:

    ```bash
    cp docker-compose.yml $PIM_DIR/
    cp docker-compose.override.yml.dist $PIM_DIR/
    ```

    The only change is the addition of a new container `object-storage`, based on `minio` (see https://min.io/),
    and compatible with the Amazon S3 protocol. This container allows testing object storage configuration for assets and media.
    
    Don't forget to restart your docker instance:

    ```bash
    cd $PIM_DIR
    docker-compose up -d
    cd -
    ```
    
    ```bash
    cp app/config/parameters.yml.dist $PIM_DIR/app/config/
    ```
    
    The only change is the addition of a new parameter `asset_index_name` that is the name of the Elasticsearch's index for the new Assets Manager feature.
     
4. Update your **app/config/config\*.yml** files

    The **app/config/config.yml** file didn't change between 3.1 and 3.2.
    The **app/config/config_dev.yml** file didn't change between 3.1 and 3.2.
    The **app/config/config_behat.yml** file didn't change between 3.1 and 3.2.

    Apply the changes for **config_prod**, from the standard edition directory:

    ```bash
    cp app/config/config_prod.yml $PIM_DIR/app/config/
    ```
    
    The `authentication` monolog handler has been updated in favor of a dedicated service.

5. Update your **app/config/routing.yml** file

    Apply the changes for **routing.yml**, from the standard edition directory:

    ```bash
    cp app/config/routing.yml $PIM_DIR/app/config/
    ```

6. Update your **app/AppKernel.php**:
    
    Apply the changes, from the standard edition directory:
    
    ```bash
    cp app/AppKernel.php $PIM_DIR/app/
    # then add your own bundles
    ```
        
    The only change is the addition of the bundle `Akeneo\AssetManager\Infrastructure\Symfony\AkeneoAssetManagerBundle()` in the method `getPimEnterpriseBundles`.
        
7. Deactivate your custom code

Before updating the dependencies and migrating your data, please deactivate all your custom bundles and configuration. This will considerably ease the migration of your data. You can disable your custom code by commenting out your custom bundles in your `AppKernel.php` file.

8. Update your PHP dependencies

   From the downloaded archive:

    ```bash
    cp composer.json $PIM_DIR/
    # then add your own dependencies
    ```

    The following PHP dependencies have changed:
     - `symfony/symfony` upgraded to 3.4.28
     - `twig/twig` upgraded to 1.42.2
     - `league/flysystem-aws-s3-v3` 1.0 added

    Now we are ready to update the backend dependencies:

    ```bash
    cd $PIM_DIR
    php -d memory_limit=3G composer update
    cd -
    ```

     **This step will copy the upgrades folder from `pim-enterprise-dev/` and `pim-community-dev` to your Pim project root in order to migrate.**
    If you have custom code in your project, this step may raise errors in the "post-script" command.
    In this case, go to the chapter "Migrate your custom code" before running the database migration.

9. Update your JS dependencies

   From the downloaded archive:

    ```bash
    cp package.json $PIM_DIR/
    # then add your own dependencies
    ```
    
    Now we are ready to update the frontend dependencies:

    ```bash
    cd $PIM_DIR
    yarn install
    cd -
    ```
    
    The following JS dependencies have changed:
      - `cucumber-html-reporter` upgraded to 5.0.0
      - `eslint` upgraded to 6.0.1
      - `jquery` upgraded to 3.4.0
      - `lodash` upgraded to 4.17.14
      - `@types/react` upgraded to ^16.8.0
      - `@types/react-dom` upgraded to ^16.8.0 

10. Migrate your MySQL database:

    Please, make sure the folder `upgrades/schema/` does not contain former migration files (from PIM 3.0 to 3.1 for instance), otherwise the migration command will surely not work properly.

    ```bash
    cd $PIM_DIR
    rm -rf var/cache
    bin/console doctrine:migration:migrate --env=prod
    ```

11. Migrate your Elasticsearch indices

    In case you updated the settings of Elasticsearch (like normalizers, filters and analyzers), please make sure you properly loaded your custom settings in the [Elasticsearch configuration](https://github.com/akeneo/pim-enterprise-standard/blob/3.1/app/config/pim_parameters.yml#L58-L68).

    Same in case you have a big catalog and increased the [index.mapping.total_fields.limit](https://www.elastic.co/guide/en/elasticsearch/reference/6.5/mapping.html#mapping-limit-settings). Make sure you properly loaded your custom settings in the [Elasticsearch configuration](https://github.com/akeneo/pim-community-standard/blob/3.1/app/config/pim_parameters.yml#L55-L57).

    As of PIM v3.2, we now take advantage of [Elasticsearch's aliases](https://www.elastic.co/guide/en/elasticsearch/reference/6.5/indices-aliases.html). Thus, all indices have to be reindexed.

    Also, as Elasticsearch does not take into account case insensitivity of option codes when searching and as we modified the way products values are loaded from MySQL, Elasticsearch search has to be case insensitive when searching on option codes. Thus, all mappings have to updated.
    
    You also need to create a new index for the Asset Manager feature.

    To take into account those two changes:

    ```bash
    php upgrades/schema/es_20190715140437_ee_create_asset_manager_index.php
    php bin/console akeneo:elasticsearch:update-mapping -e prod --all
    ```

## Migrate your custom code

1. Apply the sed commands

Several classes and services have been moved or renamed. The following commands help to migrate references to them:

```bash
find ./src/ -type f -print0 | xargs -0 sed -i 's#Akeneo\\Pim\\Enrichment\\Bundle\\Elasticsearch\\Filter\\Field\\AncestorFilter#Akeneo\\Pim\\Enrichment\\Bundle\\Elasticsearch\\Filter\\Field\\AncestorIdFilter#g'
find ./src/ -type f -print0 | xargs -0 sed -i 's#Akeneo\\Pim\\Enrichment\\Component\\Product\\Factory\\ValueCollectionFactory#Akeneo\\Pim\\Enrichment\\Component\\Product\\Factory\\WriteValueCollectionFactory#g'
find ./src/ -type f -print0 | xargs -0 sed -i 's#Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ValueCollection#Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\WriteValueCollection#g'
find ./src/ -type f -print0 | xargs -0 sed -i 's#Akeneo\\Pim\\Enrichment\\Bundle\\Storage\\ORM\\Connector\\GetConnectorProductModels#Akeneo\\Pim\\Enrichment\\Bundle\\Storage\\Sql\\Connector\\SqlGetConnectorProductModels#g'
find ./src/ -type f -print0 | xargs -0 sed -i 's#ValueCollectionIn\\Pim\\Enrichment\\Bundle\\Storage\\ORM\\Connector\\GetConnectorProductModels#Akeneo\\Pim\\Enrichment\\Bundle\\Storage\\Sql\\Connector\\SqlGetConnectorProductModels#g'
find ./src/ -type f -print0 | xargs -0 sed -i 's/ValueCollectionInterface/WriteValueCollectionInterface/g'
```

2. Adapt your custom codes to handle the breaking changes we introduced:

 - Service `pim_catalog.saver.channel` class has been changed to `Akeneo\Channel\Bundle\Storage\Orm\ChannelSaver`.
 - Interface `Akeneo\Channel\Component\Model\ChannelInterface` has a new method `popEvents(): array`
 - The following classes have been removed:

   - `Akeneo\Pim\Enrichment\Bundle\EventSubscriber\RemoveUserSubscriber`. This subscriber has been replaced by a proper Doctrine mapping that set the user at `null` in the `Comment` entity for which the user is the author when the user is removed. If you override this service, you can use the same events (`StorageEvents::PRE_REMOVE` and `StorageEvents::POST_REMOVE`) on `Akeneo\UserManagement\Component\Model\UserInterface`to fire your own subscriber.
   - `Akeneo\Pim\Enrichment\Bundle\Storage\ORM\Connector\GetConnectorProductsFromWriteModel`
   - `Akeneo\Pim\Enrichment\Bundle\Storage\ORM\Connector\GetMetadataForProductModel`
   - `Akeneo\Pim\Enrichment\Component\Product\Query\GetMetadata`
   - `Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetMetadataInterface`
   - `Akeneo\Pim\Enrichment\Component\Product\Query\GetMetadataInterface`. These class and interface have been removed from the refactoring of the `Akeneo\Pim\Enrichment\Bundle\Storage\ORM\Connector\GetConnectorProductModels`. You can check the new class `Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Connector\SqlGetConnectorProductModels` to see how it has been replaced. 
   - `Akeneo\Pim\Enrichment\Component\Product\Factory\ValueCollectionFactoryInterface`
   - `Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface`. These interfaces have been removed. You can now directly extends `Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory` and `Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection`
   
3. Reactivate your custom code

You are now ready to reactivate your custom bundles in the `AppKernel.php` file.

4. Then re-generate the PIM assets:

    ```bash
    bin/console cache:clear --env=prod
    bin/console pim:installer:assets --clean --env=prod
    yarn run less
    yarn run webpack
    ```

5. Restart the queue consumer

Now you are ready to restart the queue consumer daemon.

If you use `supervisor`, then restart your daemon as following:

    ```bash
    supervisorctl status
    # the command returns the following daemons
    # pim-queue-daemon:pim-queue-daemon_00 STOPPED    Jan 24 11:41 AM

    supervisorctl start pim-queue-daemon:pim-queue-daemon_00

    supervisorctl status
    # pim-queue-daemon:pim-queue-daemon_00 RUNNING    pid 3500, uptime 0:00:04
    # the daemon has been restarted
    ```

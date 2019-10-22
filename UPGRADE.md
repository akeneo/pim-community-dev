# UPGRADE FROM 3.1 TO 3.2

Use this documentation to migrate projects based on the Community Edition.

## Disclaimer

> When starting your upgrade, make sure:
>  - you created your project from the standard distribution.
>  - you performed a backup of your database before proceeding to the migration. You can use tools like [mysqldump](https://dev.mysql.com/doc/refman/5.7/en/mysqldump.html).
>  - you performed a backup of your indices before proceeding to the migration. You can use Elastisearch API [_snapshot](https://www.elastic.co/guide/en/elasticsearch/reference/6.5/modules-snapshots.html).
>  - you performed a backup of your codebase if you don't use a VCS (Version Control System).

## Requirements

Make sure that you're using Akeneo PIM v3.1. You can check this information at the bottom of the dashboard.

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

    To give you a quick overview of the changes made to a standard project, you can check on [Github](https://github.com/akeneo/pim-community-standard/compare/3.1...3.2).

    The `$PIM_DIR` variable will contain the path to your current PIM installation:


    ```bash
    export PIM_DIR=/path/to/your/current/pim/installation
    ```

2. Download the latest standard edition from the website [PIM community standard](http://www.akeneo.com/download/) and extract:

    ```bash
    wget http://download.akeneo.com/pim-community-standard-v3.2-latest.tar.gz
    tar -zxf pim-community-standard-v3.2-latest.tar.gz
    cd pim-community-standard/
    ```

3. Update the configuration files:

4. Update your **app/config/config.yml**


5. Update your **app/config/config_dev.yml** and **app/config/config_prod.yml**


6. Update your **app/config/config_behat.yml** and **app/config/config_test.yml**:


7. Update your **app/AppKernel.php**:

8. Deactivate your custom code

Before updating the dependencies and migrating your data, please deactivate all your custom bundles and configuration. This will considerably ease the migration of your data. You can disable your custom code by commenting out your custom bundles in your `AppKernel.php` file.

9. Update your PHP dependencies

   From the downloaded archive:

    ```bash
    cp composer.json $PIM_DIR/
    # then add your own dependencies
    ```
    Now we are ready to update the backend dependencies:

    ```bash
    cd $PIM_DIR
    php -d memory_limit=3G composer update
    ```

     **This step will copy the upgrades folder from `pim-community-dev/` to your Pim project root in order to migrate.**
    If you have custom code in your project, this step may raise errors in the "post-script" command.
    In this case, go to the chapter "Migrate your custom code" before running the database migration.

10. Update your JS dependencies

   From the downloaded archive:

    ```bash
    cp package.json $PIM_DIR/
    # then add your own dependencies
    ```

11. Migrate your MySQL database

Please, make sure the folder upgrades/schema/ does not contain former migration files (from PIM 3.0 to 3.2 for instance), otherwise the migration command will surely not work properly.

    ```bash
    rm -rf var/cache
    bin/console doctrine:migration:migrate --env=prod
    ```

## Migrate your custom code

1. Apply the sed commands

   Several classes and services have been moved or renamed. The following commands help to migrate references to them:

    ```bash
    find ./src/ -type f -print0 | xargs -0 sed -i 's#Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber\AddVersionSubscriber#Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber\AddVersionListener#g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's#Akeneo\UserManagement\Bundle\EventListener\UserPreferencesSubscriber#Akeneo\UserManagement\Bundle\EventListener\UserPreferencesListener#g'
    ```

2. Adapt your custom codes to handle this breaking changes we introduced:

For the creation of the product values, we changed the implementation of the factory. You have to use `Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory::createByCheckingData` if you create a value coming from the outside world (CSV import, UI, API).

If you want to create a value and the data is loaded from the database, you should use `Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory::createWithoutCheckingData`. This has been done for performance purpose: the checks are time consuming and useless as data is already validated and persisted in database.

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
    # the command returns the following daemons
    # pim-queue-daemon:pim-queue-daemon_00 STOPPED    Jan 24 11:41 AM

    supervisorctl start pim-queue-daemon:pim-queue-daemon_00

    supervisorctl status
    # pim-queue-daemon:pim-queue-daemon_00 RUNNING    pid 3500, uptime 0:00:04
    # the daemon has been restarted
```

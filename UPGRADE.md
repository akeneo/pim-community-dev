# UPGRADE FROM 3.1 TO 3.2

## Disclaimer

> Please check that you're using Akeneo PIM v3.1.

> We're assuming that you created your project from the standard distribution.

> This documentation helps to migrate projects based on the Enterprise Edition.

> Please perform a backup of your database before proceeding to the migration. You can use tools like [mysqldump](https://dev.mysql.com/doc/refman/5.7/en/mysqldump.html).

> Please perform a backup of your indices before proceeding to the migration. You can use Elastisearch API [_snapshot](hhttps://www.elastic.co/guide/en/elasticsearch/reference/6.5/modules-snapshots.html).

> Please perform a backup of your codebase if you don't use a VCS (Version Control System).

## Requirements

Please, see the complete [list of requirements](https://docs.akeneo.com/3.2/install_pim/manual/system_requirements/system_requirements.html) for PIM v3.2. The requirements are exactly the same that PIM v3.1.

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

    An easy way to update them is to copy/paste configuration files from the latest standard edition. Normally you shouldn't have made a single change to them in your project. If it's the case, don't forget to update them with your changes.

    First, we'll consider you have a `$PIM_DIR` variable:

    ```bash
    export PIM_DIR=/path/to/your/current/pim/installation
    ```
    
    Then apply the changes:

    ```bash
    cp .env.dist $PIM_DIR/
    cp .env.dist $PIM_DIR/.env
    cp .gitignore $PIM_DIR/
    cp docker-compose.override.yml.dist $PIM_DIR/
    cp docker-compose.yml $PIM_DIR/

    cp app/PimRequirements.php $PIM_DIR/app/
    cp app/config/pim_parameters.yml $PIM_DIR/app/config/
    cp app/config/security.yml $PIM_DIR/app/config/
    cp app/config/security_test.yml $PIM_DIR/app/config/
    ```

    Or you can follow the detailed list of changes:

4. Update your **app/config/config.yml**

    An easy way to update it is to copy/paste from the latest standard edition and add your custom changes.

    ```bash
    cp app/config/config.yml $PIM_DIR/app/config/
    # then add your own changes
    ```

    Or you can update the file with the following changes:

5. Update your **app/config/config_dev.yml**

    An easy way to update it is to copy/paste from the latest standard edition and add your custom changes.

    ```bash
    cp app/config/config_dev.yml $PIM_DIR/app/config
    # then add your own changes
    ```

    Or you can update the file with the following changes:

6. Update your **app/config/config_behat.yml**

    An easy way to update it is to copy/paste from the latest standard edition and add your custom changes.

    ```bash
    cp app/config/config_behat.yml $PIM_DIR/app/config
    # then add your own changes
    ```

    Or you can update the file with the following changes:

7. Update your **app/config/config_prod.yml**

    An easy way to update it is to copy/paste from the latest standard edition and add your custom changes.

    ```bash
    cp app/config/config_prod.yml $PIM_DIR/app/config
    # then add your own changes
    ```

    Or you can update the file with the following changes:
            
8. Update your **app/AppKernel.php**:
    
    An easy way to update it is to copy/paste from the latest standard edition and add your own bundles in the `registerProjectBundles` method.
    
    ```bash
    cp app/AppKernel.php $PIM_DIR/app/
    # then add your own bundles
    ```
        
9. Deactivate your custom code

Before updating the dependencies and migrating your data, please deactivate all your custom bundles and configuration. This will considerably ease the migration of your data. You can disable your custom code by commenting out your custom bundles in your `AppKernel.php` file.

10. Update your dependencies:

    The easiest way to update your `composer.json` is to copy/paste from the latest standard edition and add your custom dependencies.
    
    ```bash
    cp composer.json $PIM_DIR/
    # then add your own dependencies
    ```
    
    If you don't, make sure you have updated Akeneo PIM dependencies and also that you have the following `post-update-cmd` task:
    
    ```yaml
        "post-update-cmd": [
            "@symfony-scripts",
            "Akeneo\\Platform\\Bundle\\InstallerBundle\\EnterpriseComposerScripts::copyUpgradesFiles"
        ]
    ```
    
    The easiest way to update your `package.json` is to copy/paste from the latest standard edition and add your custom dependencies.
    
    ```bash
    cp package.json $PIM_DIR/
    # then add your own dependencies
    ```
    
    Now we are ready to update the backend dependencies:
    
    ```bash
    cd $PIM_DIR
    php -d memory_limit=3G composer update
    ```
    
     **This step will copy the upgrades folder from `pim-enterprise-dev/` to your Pim project root in order to migrate.**
    If you have custom code in your project, this step may raise errors in the "post-script" command.
    In this case, go to the chapter "Migrate your custom code" before running the database migration.

    And we also have to update the frontend dependencies:
    
    ```bash
    yarn install
    ```

12. Migrate your MySQL database: 

    Please, make sure the folder `upgrades/schema/` does not contain former migration files (from PIM 2.2 to 2.3 for instance), 
    otherwise the migration command will surely not work properly.

    ```bash
    rm -rf var/cache
    bin/console doctrine:migration:migrate --env=prod
    ```

13. Migrate your Elasticsearch indices:

    In case you updated the settings of Elasticsearch (like normalizers, filters and analyzers), please make sure you properly loaded your custom settings in the [Elasticsearch configuration](https://github.com/akeneo/pim-enterprise-standard/blob/3.1/app/config/pim_parameters.yml#L58-L68).

    Same in case you have a big catalog and increased the [index.mapping.total_fields.limit](https://www.elastic.co/guide/en/elasticsearch/reference/6.5/mapping.html#mapping-limit-settings). Make sure you properly loaded your custom settings in the [Elasticsearch configuration](https://github.com/akeneo/pim-enterprise-standard/blob/3.1/app/config/pim_parameters.yml#L58-L68).

    ```bash
    php bin/console akeneo:elasticsearch:update-mapping --all
    ```

## Migrate your custom code

1. Apply the sed commands

Several classes and services have been moved or renamed. The following commands help to migrate references to them:

```bash
```

2. Reactivate your custom code

You are now ready to re enable your custom bundles in the `AppKernel.php` file. 

3. Restart the queue consumer

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

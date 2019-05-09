# UPGRADE FROM 3.1 TO 3.2

## Disclaimer

> Please check that you're using Akeneo PIM v3.1.

> We're assuming that you created your project from the standard distribution.

> This documentation helps to migrate projects based on the Community Edition.

> Please perform a backup of your database before proceeding to the migration. You can use tools like [mysqldump](https://dev.mysql.com/doc/refman/5.7/en/mysqldump.html).

> Please perform a backup of your indices before proceeding to the migration. You can use Elastisearch API [_snapshot](https://www.elastic.co/guide/en/elasticsearch/reference/6.5/modules-snapshots.html).

> Please perform a backup of your codebase if you don't use a VCS (Version Control System).

## Requirements

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

2. Download the latest standard edition from the website [PIM community standard](http://www.akeneo.com/download/) and extract:

    ```bash
    wget http://download.akeneo.com/pim-community-standard-v3.2-latest.tar.gz
    tar -zxf pim-community-standard-v3.2-latest.tar.gz
    cd pim-community-standard/
    ```

3. Update the configuration files:

    An easy way to update them is to copy/paste configuration files from the latest standard edition. Normally you shouldn't have made a single change to them in your project. If it's the case, don't forget to update them with your changes.

    First, we'll consider you have a `$PIM_DIR` variable:

    ```bash
    export PIM_DIR=/path/to/your/current/pim/installation
    ```

    Then apply the changes:
    
    TODO: ADD ALL THE CHANGES HERE

4. Update your **app/config/config.yml**

    An easy way to update it is to copy/paste from the latest standard edition and add your custom changes.

    ```bash
    cp app/config/config.yml $PIM_DIR/app/config/
    # then add your own changes
    ```

    Or you can update the file with the following changes:
    
    TODO: ADD ALL THE CHANGES HERE

5. Update your **app/config/config_dev.yml** and **app/config/config_prod.yml**

    An easy way to update it is to copy/paste from the latest standard edition and add your custom changes.

    ```bash
    cp app/config/config_dev.yml $PIM_DIR/app/config/
    cp app/config/config_prod.yml $PIM_DIR/app/config/
    # then add your own changes
    ```

    Or you can update the files with the following changes:

    TODO: ADD ALL THE CHANGES HERE

6. Update your **app/config/config_behat.yml** and **app/config/config_test.yml**:

    An easy way to update it is to copy/paste from the latest standard edition and add your custom changes.

    ```bash
    cp app/config/config_behat.yml $PIM_DIR/app/config/
    cp app/config/config_test.yml $PIM_DIR/app/config/
    # then add your own changes
    ```

    Or you can update the files with the following changes:
    
    TODO: ADD ALL THE CHANGES HERE

7. Update your **app/AppKernel.php**:

    An easy way to update it is to copy/paste from the latest standard edition and add your own bundles in the `registerProjectBundles` method.

    ```bash
    cp app/AppKernel.php $PIM_DIR/app/
    # then add your own bundles
    ```
    Or you can follow the detailed list of changes:

    TODO: ADD ALL THE CHANGES HERE

8. Deactivate your custom code

Before updating the dependencies and migrating your data, please deactivate all your custom bundles and configuration. This will considerably ease the migration of your data. You can disable your custom code by commenting out your custom bundles in your `AppKernel.php` file.

9. Update your dependencies:

    The easiest way to update your `composer.json` is to copy/paste from the latest standard edition and add your custom dependencies.

    ```bash
    cp composer.json $PIM_DIR/
    # then add your own dependencies
    ```

    If you don't, make sure you have updated Akeneo PIM dependencies and also that you have the following `post-update-cmd` task:

    ```json
    "post-update-cmd": [
        "@symfony-scripts",
        "Akeneo\\Platform\\Bundle\\InstallerBundle\\ComposerScripts::copyUpgradesFiles"
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

     **This step will copy the upgrades folder from `pim-community-dev/` to your Pim project root in order to migrate.**
    If you have custom code in your project, this step may raise errors in the "post-script" command.
    In this case, go to the chapter "Migrate your custom code" before running the database migration.

    And we also have to update the frontend dependencies:

    ```bash
    yarn install
    ```

## Migrate your custom code

1. Apply the sed commands

Several classes and services have been moved or renamed. The following commands help to migrate references to them:

```bash
    TODO: ADD THE SEDs
```

2. Adapt your custom codes to handle this breaking changes we introduced:

 - Service `pim_catalog.saver.channel` class has been changed to `Akeneo\Channel\Bundle\Storage\Orm\ChannelSaver`.
 - Interface `Akeneo\Channel\Component\Model\ChannelInterface` has a new methods `popEvents(): array`

3. Reactivate your custom code

You are now ready to re enable your custom bundles in the `AppKernel.php` file.

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

# UPGRADE FROM 3.0 TO 3.1

## Disclaimer

> Please check that you're using Akeneo PIM v3.0.

> We're assuming that you created your project from the standard distribution.

> This documentation helps to migrate projects based on the Community Edition.

> Please perform a backup of your database before proceeding to the migration. You can use tools like [mysqldump](https://dev.mysql.com/doc/refman/5.7/en/mysqldump.html).

> Please perform a backup of your indices before proceeding to the migration. You can use Elastisearch API [_snapshot](hhttps://www.elastic.co/guide/en/elasticsearch/reference/6.5/modules-snapshots.html).

> Please perform a backup of your codebase if you don't use a VCS (Version Control System).

## Requirements

Please, see the complete [list of requirements](https://docs.akeneo.com/3.1/install_pim/manual/system_requirements/system_requirements.html) for PIM v3.1.

Please provide a server with the following requirements before proceeding to the PIM 3.1 migration. To install those requirements, you can follow the official documentations or our installation documentation on [Debian 9](https://docs.akeneo.com/3.1/install_pim/manual/system_requirements/manual_system_installation_debian9.html) or [Ubuntu 16.04](https://docs.akeneo.com/3.1/install_pim/manual/system_requirements/system_install_ubuntu_1604.html).

### PHP Version

Akeneo PIM v3.1 now expects PHP ...

### MySQL version

Akeneo PIM v3.1 now expects MySQL ...

### Elasticsearch version

Akeneo PIM v3.1 now expects Elasticsearch ...

### Node version

Akeneo PIM v3.1 now expects Node ...

## The main changes of the 3.1 version

...

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

To give you a quick overview of the changes made to a standard project, you can check on [Github](https://github.com/akeneo/pim-community-standard/compare/3.0...3.1).

2. Download the latest standard edition and extract it

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
    cp docker/sso_authsources.php $PIM_DIR/docker/

    cp app/PimRequirements.php $PIM_DIR/app/
    cp app/config/pim_parameters.yml $PIM_DIR/app/config/
    cp app/config/security.yml $PIM_DIR/app/config/
    cp app/config/security_test.yml $PIM_DIR/app/config/
    ```

    Or you can follow the detailed list of changes:

    * The `.env.dist`, `docker-compose.override.yml.dist`, `docker/sso_authsources.php`, `docker-compose.yml`, `.gitignore`, `app/PimRequirements.php` have completely changed ot didn't exist before, it's better to copy them.


    * The configuration file `app/config/security.yml` had somes changes:

        - ...

    * The configuration file `app/config/pim_parameters.yml` had some changes:

        - ...

    * The configuration file `app/config/security_test.yml` had some changes:

        - ...

4. Update your **app/config/config.yml**

    An easy way to update it is to copy/paste from the latest standard edition and add your custom changes.

    ```bash
    cp app/config/config.yml $PIM_DIR/app/config
    # then add your own changes
    ```

    Or you can follow the detailed list of changes:

        - ...

5. Update your **app/config/config_prod.yml**

    An easy way to update it is to copy/paste from the latest standard edition and add your custom changes.

    ```bash
    cp app/config/config_prod.yml $PIM_DIR/app/config
    # then add your own changes
    ```

    Or you can update the file with the following changes:

    - ...

6. Update your **app/config/routing.yml**

    An easy way to update it is to copy/paste from the latest standard edition and add your custom changes.

    ```bash
    cp app/config/routing.yml $PIM_DIR/app/config
    # then add your own changes
    ```

    Or you can follow the detailed list of changes:

    * The following route configurations have been removed:
        - `old_route`

    * The following route configurations have been added:

        v3.1:
        ```yaml
        route_name:
            resource: "@YOUR_NEW_BUNDLE/config/routing/routing.yml"
        ```

    * The following route configurations have been updated:

        - route_name

        v3.0.x:
        ```yaml
        route_name:
            resource: "@YOUR_OLD_BUNDLE/config/routing/routing.yml"
        ```

        to

        v3.1:
        ```yaml
        route_name:
            resource: "@YOUR_NEW_BUNDLE/config/routing/routing.yml"
        ```

7. Update your **app/AppKernel.php**:

    An easy way to update it is to copy/paste from the latest standard edition and add your own bundles in the `registerProjectBundles` method.

    ```bash
    cp app/AppKernel.php $PIM_DIR/app/
    # then add your own bundles
    ```
    Or you can follow the detailed list of changes:

    * The following bundles have been renamed:
        - `THE_OLD_BUNDLE` now is `THE_NEW_BUNDLE`

    * The following bundles have been removed:
        - `TO_FILL`

    * The following bundles have been added (order of declaration is important):
        - `TO_FILL`


9. Add new parameters to `parameters.yml`

TO_FILL_IF_NEEDED

10. Deactivate your custom code

Before updating the dependencies and migrating your data, please deactivate all your custom bundles and configuration. This will considerably ease the migration of your data. You can disable your custom code by commenting out your custom bundles in your `AppKernel.php` file.

11. Update your dependencies:

    The easiest way to update your `composer.json` is to copy/paste from the latest standard edition and add your custom dependencies.

    ```bash
    cp composer.json $PIM_DIR/
    # then add your own dependencies
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

12. Migrate your MySQL database:

    Please, make sure the folder `upgrades/schema/` does not contain former migration files (from PIM 3.0 to 3.1 for instance),
    otherwise the migration command will surely not work properly.

    ```bash
    rm -rf var/cache
    bin/console doctrine:migration:migrate --env=prod
    ```

13. Migrate your Elasticsearch indices:

    In case you updated the settings of Elasticsearch (like normalizers, filters and analyzers), please make sure you properly loaded your custom settings in the [Elasticsearch configuration](https://github.com/akeneo/pim-community-standard/blob/3.1/app/config/pim_parameters.yml#L58-L68).

    Same in case you have a big catalog and increased the [index.mapping.total_fields.limit](https://www.elastic.co/guide/en/elasticsearch/reference/6.5/mapping.html#mapping-limit-settings). Make sure you properly loaded your custom settings in the [Elasticsearch configuration](https://github.com/akeneo/pim-community-standard/blob/3.1/app/config/pim_parameters.yml#L58-L68).

    ```bash
    TO_FILL_WITH_NEW_SCRIPTS
    ```

14. Migrate your .less assets

    If you have defined a Resources/config/assets.yml file in any of your bundles to import .less files, you must move these imports to a new file at Resources/public/less/index.less to import your styles instead.

    For example

    Before in `Resources/config/assets.yml`
    ```yml
        css:
            lib:
                - bundles/yourbundle/assets/less/styles.css
                - bundles/yourbundle/assets/less/bundle.less
    ```

    After in `Resources/public/less/index.less`

    ```less
        @import (less) "./web/bundles/yourbundle/assets/less/styles.css";
        @import "./web/bundles/yourbundle/assets/less/bundle.less";
    ```

    If you are importing a .css file, you must add `(less)` after the import, as above. If you only have .less files in your bundle's assets.yml, you can remove it.

15. Then re-generate the PIM assets:

    ```bash
    bin/console pim:installer:assets --clean --env=prod
    yarn run less
    yarn run webpack
    ```

## Migrate your custom code

1. Apply the sed commands

Several classes and services have been moved or renamed. The following commands help to migrate references to them:

```bash
find ./src/ -type f -print0 | xargs -0 sed -i 's/PATTERN_TO_REPLACE/NEW_PATTERN/g'
```

2. Reactivate your custom code

You are now ready to re enable your custom bundles in the `AppKernel.php` file. The following changes may affect your developments:

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

# UPGRADE FROM 3.0 TO 3.1

## Disclaimer

> Please check that you're using Akeneo PIM v3.0.

> We're assuming that you created your project from the standard distribution.

> This documentation helps to migrate projects based on the Enterprise Edition.

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


1. Migrate your .less assets

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

2. Then re-generate the PIM assets:

    ```bash
    bin/console pim:installer:assets --clean --env=prod
    yarn run less
    yarn run webpack
    ```

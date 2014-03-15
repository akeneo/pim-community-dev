UPGRADE FROM 1.0 to 1.1
=======================

General
-------

- Update the doctrine schema with php app/console doctrine:schema:update --force to :
 - remove the `fallback` field in the `Locale` entity
 - add the `properties` field in the `Attribute` entity
 - create the `pim_enrich_datagrid_view` and `pim_enrich_datagrid_configuration` tables

- Update the assets to get new js filters, css with `php app/console pim:install --task=assets --force`

CustomEntityBundle
------------------

The CustomEntityBundle has been moved in its own repository to allow a different release cycle.

The purpose of this bundle is to eases the creation of your own entities and related screen for a custom project.

If you are using the CustomEntityBundle, please add "akeneo/custom-entity-bundle" to your composer.json

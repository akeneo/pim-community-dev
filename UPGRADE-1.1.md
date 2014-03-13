UPGRADE FROM 1.0 to 1.1
=======================

- Update the schema (php app/console doctrine:schema:update --force) to :
 - remove the `fallback` field in the `Locale` entity
 - add the `properties` field in the `Attribute` entity
 - create the `pim_enrich_datagrid_view` and `pim_enrich_datagrid_configuration` tables
- If you are using the CustomEntityBundle, please add "akeneo/custom-entity-bundle" to your composer.json

# How to activate/deactivate attribute type depending on a feature flag


This mini documentation explains how to activate/deactivate an attribute type for the Product/Product Model/ Draft and Published products.
Here, it's an example to activate/deactivate an asset attribute type depending on the activation of the `asset_manager` feature or not.


## Technical explanation

Attribute types are registered in a service `AttributeRegistry`. All supported attribute types are loaded inside this registry with a compiler pass `RegisterAttributeTypePass`.

To register a new attribute type available only if the associated feature flag is activated, you have to configure in the DI the `feature` key in `tags`: 

```
    akeneo_assetmanager.attribute_type.asset_collection:
        class: Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType
        arguments:
            - 'reference_data_options'
        tags:
            - { name: pim_catalog.attribute_type, alias: pim_catalog_asset_collection, entity: '%pim_catalog.entity.product.class%', feature: 'asset_manager' }
```

Note: they key `features` is not mandatory. If not provided, the attribute type is always activated.

## Impacts

- An attribute of type "asset" cannot be created from any source (import, API, UI) if the feature `asset_manager` is not activated.
- The UI does not offer the choice to create an asset attribute type. It's automatically hidden.

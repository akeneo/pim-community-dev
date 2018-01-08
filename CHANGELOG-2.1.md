# 2.1.X

## Bug fixes

- PIM-7074: Fix label and completeness filters for search

## BC breaks

### Services

- Rename the service `pim_enrich.normalizer.product_model_incomplete_values` to `pim_enrich.normalizer.incomplete_values` because it works not only for product models

### Constructors

- Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\ProductNormalizer` to add a `Pim\Bundle\EnrichBundle\Normalizer\IncompleteValuesNormalizer` as last argument

# 2.1.0 (2017-12-21)

## Better manage products with variants

- PIM-7012: Add mass association on products 
- PIM-7033: Allow user to select product models as associations in UI
- PIM-6652: Add a parent filter and column into the product grid
- PIM-6924: Display the total missing required attributes on the PEF for products and product models
- PIM-6924: Add links on the PEF to navigate to missing required attribute if attribute is on a parent
- PIM-6924: Add links on the PEF completeness dropdown to go to missing required attribute, even if it's on a parent
- PIM-7055: No product version was added when associating it with a product model, it's now fixed
- PIM-6361: Manage the add categories bulk action for product models
- PIM-6982: Manage the remove categories bulk action for product models
- PIM-6983: Manage the move categories bulk action for product models

## Web API

- API-415: Update a list of options of a simple or multi select attribute

## BC breaks

### Constructors

- Change the constructor of `Pim\Bundle\ApiBundle\Controller\AttributeOptionController` to add `Pim\Bundle\ApiBundle\Stream\StreamResourceResponse` paramater

# 2.1.0-ALPHA2 (2017-12-15)

## Better manage products with variants

- PIM-6996: Associate products to product models during import using the `<assocType>-product_models` pattern in an new column
- PIM-6998: Export association from products to product models using the `<assocType>-product_models` pattern in an new column

## Have a better UX/UI

- PIM-6342: Display and remove associations in a gallery view
- PIM-7051: Add images to associated products that have asset collection as main image
- PIM-7046: Add ability to customise empty grid message and illustration for associations
- PIM-7028: Use the search in the association picker 
- PIM-6917: Fix CSS glitches

## Web API

- API-443: Prevent getting asset via media file url of the API

## Migrations

Please run the doctrine migrations command in order to use the new mass edit: `bin/console doctrine:migrations:migrate --env=prod`

## BC breaks

### Interfaces

Removed typehint of ProductInterface in the Pim\Component\Catalog\Updater\Adder\FieldAdderInterface and Pim\Component\Catalog\Updater\Adder\AttributeAdderInterface
Removed typehint of ProductInterface in the Pim\Component\Catalog\Updater\Remover\FieldRemoverInterface and Pim\Component\Catalog\Updater\Remover\AttributeRemoverInterface
Removed typehint of ProductInterface in the Pim\Component\Catalog\Updater\Setter\FieldSetterInterface and Pim\Component\Catalog\Updater\Setter\AttributeSetterInterface
- Change the constructor of `Pim\Bundle\DataGridBundle\Normalizer\ProductAssociationNormalizer` to add `Pim\Bundle\EnrichBundle\Normalizer\ImageNormalizer` parameter

# 2.1.0-ALPHA1

## Have a better UX/UI

- PIM-6480: Add gallery view and display selector to the product grid
- PIM-6621: Add search on label and identifier on products and product models in the product grid
- PIM-6990: Add new screen for managing product associations

## Better manage products with variants
- PIM-6966: Add tracker information for product model, product variant and family variant

## BC breaks

### Constructors

- Change the constructor of `Pim\Bundle\DataGridBundle\Normalizer\ProductNormalizer` to add `Pim\Bundle\EnrichBundle\Normalizer\ImageNormalizer` parameter
- Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\EntityWithFamilyVariantNormalizer` to replace `Pim\Bundle\EnrichBundle\Normalizer\FileNormalizer` parameter by `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\ProductModelNormalizer` to replace `Symfony\Component\Serializer\Normalizer\NormalizerInterface` parameter by `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\ProductNormalizer` to replace `Symfony\Component\Serializer\Normalizer\NormalizerInterface` parameter by `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
- Change the constructor of `Pim\Component\Catalog\Validator\Constraints\FamilyAttributeAsImageValidator` to add a `string[]`
- Change the constructor of `Pim\Bundle\AnalyticsBundle\DataCollector\DBDataCollector` to add a `Pim\Component\Catalog\Repository\ProductModelRepositoryInterface`, `Pim\Component\Catalog\Repository\VariantProductRepositoryInterface` and `Pim\Component\Catalog\Repository\FamilyVariantRepositoryInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\ProductController` to add `Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface` as second parameter
- Change the constructor of `Pim\Component\Catalog\Updater\Adder\AssociationFieldAdder` to add `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface` parameter as a 2nd argument
- Change the constructor of `Pim\Component\Catalog\Updater\Adder\AssociationFieldSetter` to add `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface` parameter as a 2nd argument

### Methods

- Add optional parameter `$scopeCode` to the method `getLabel` of `Pim\Component\Catalog\Model\ProductModelInterface`
- Add optional parameter `$scopeCode` to the method `getLabel` of `Pim\Component\Catalog\Model\ProductInterface`
- Remove method `countAll` in `Pim\Component\Catalog\Repository\FamilyInterface`, `Pim\Component\Catalog\Repository\VariantProductRepositoryInterface` and `Pim\Bundle\UserBundle\Repository\UserRepositoryInterface`
- Add `Pim\Bundle\AnalyticsBundle\Repository\EntityCountableRepository` with method `countAll`

### Interfaces

- Added `getProductModels`, `addProductModel` and `removeProductModel` to `Pim\Component\Catalog\Model\AssociationInterface`

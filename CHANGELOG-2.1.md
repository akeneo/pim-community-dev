# 2.1.x

## Improvements

- PIM-6996: Associate products to product models during import using the `<assocType>-product_models` pattern in an new column
- API-443: Prevent getting asset via media file url of the API
- PIM-6996: Associate products to product models during import using the `<assocType>-product_models` pattern in an new column
- PIM-6342: Display and remove associations gallery view
- PIM-7051: Add images to associated products that have asset collection as main image

## BC breaks
- Change the constructor of `Pim\Bundle\DataGridBundle\Normalizer\ProductAssociationNormalizer` to add `Pim\Bundle\EnrichBundle\Normalizer\ImageNormalizer` parameter

## Update jobs

IMPORTANT: In order to use the new mass edit, please execute

```
bin/console akeneo:batch:create-job internal add_to_category mass_edit add_to_category '{}' 'Mass add to categories' --env=prod
bin/console akeneo:batch:create-job internal move_to_category mass_edit move_to_category '{}' 'Mass move to categories' --env=prod
bin/console akeneo:batch:create-job internal remove_from_category mass_edit remove_from_category '{}' 'Mass remove from categories' --env=prod
```

## BC breaks

### Interfaces

Removed typehint of ProductInterface in the Pim\Component\Catalog\Updater\Adder\FieldAdderInterface and Pim\Component\Catalog\Updater\Adder\AttributeAdderInterface
Removed typehint of ProductInterface in the Pim\Component\Catalog\Updater\Remover\FieldRemoverInterface and Pim\Component\Catalog\Updater\Remover\AttributeRemoverInterface
Removed typehint of ProductInterface in the Pim\Component\Catalog\Updater\Setter\FieldSetterInterface and Pim\Component\Catalog\Updater\Setter\AttributeSetterInterface

# 2.1.0-ALPHA1

## Improvements

- PIM-6480: Add gallery view and display selector to the product grid
- PIM-6621: add search on label and code on products and product models
- PIM-6966: Add tracker information for product model, product variant and family variant
- PIM-6990: Add new screen for managing product associations

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

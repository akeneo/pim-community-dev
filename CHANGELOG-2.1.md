# 2.1.0-ALPHA1

## Improvements

- PIM-6480: Add gallery view and display selector to the product grid
- PIM-6621: add search on label and code on products and product models
- PIM-6966: Add tracker information for product model, product variant and family variant
- PIM-6990: Add new screen for managing product associations
- PIM-6996: Associate products to product models during import using the `<assocType>-product_models` pattern in an new column


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

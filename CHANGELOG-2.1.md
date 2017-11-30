# 2.1

## BC breaks

### Constructors

- Change the constructor of `Pim\Bundle\DataGridBundle\Normalizer\ProductNormalizer` to add `Pim\Bundle\EnrichBundle\Normalizer\ImageNormalizer` parameter
- Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\EntityWithFamilyVariantNormalizer` to replace `Pim\Bundle\EnrichBundle\Normalizer\FileNormalizer` parameter by `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\ProductModelNormalizer` to replace `Symfony\Component\Serializer\Normalizer\NormalizerInterface` parameter by `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\ProductNormalizer` to replace `Symfony\Component\Serializer\Normalizer\NormalizerInterface` parameter by `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
- Change the constructor of `Pim\Component\Catalog\Validator\Constraints\FamilyAttributeAsImageValidator` to add a `string[]`

### Methods

- Add optional parameter `$scopeCode` to the method `getLabel` of `Pim\Component\Catalog\Model\ProductModelInterface`
- Add optional parameter `$scopeCode` to the method `getLabel` of `Pim\Component\Catalog\Model\ProductInterface`

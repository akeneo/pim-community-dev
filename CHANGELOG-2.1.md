# 2.1

## BC breaks

### Constructors

- Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\ProductNormalizer` to remove `Pim\Bundle\EnrichBundle\Normalizer\FileNormalizer` parameter
- Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\ProductModelNormalizer` to remove `Pim\Bundle\EnrichBundle\Normalizer\FileNormalizer` parameter
- Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\EntityWithFamilyVariantNormalizer` to replace `Pim\Bundle\EnrichBundle\Normalizer\FileNormalizer` parameter by `Symfony\Component\Serializer\Normalizer\NormalizerInterface`
- Change the constructor of `Pim\Component\Catalog\Validator\Constraints\FamilyAttributeAsImageValidator` to add a `string[]`

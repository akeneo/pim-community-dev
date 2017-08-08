<?php

namespace Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModelFormat;

use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize products to the 'indexing_product_and_product_model' format.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface
{
    private const FIELD_ATTRIBUTES_IN_LEVEL = 'attributes_for_this_level';
    private const FIELD_PRODUCT_TYPE = 'product_type';

    /** @var NormalizerInterface */
    private $propertiesNormalizer;

    /**
     * @param NormalizerInterface $propertiesNormalizer
     */
    public function __construct(NormalizerInterface $propertiesNormalizer)
    {
        $this->propertiesNormalizer = $propertiesNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $data = $this->propertiesNormalizer->normalize($product, $format, $context);


        $data[self::FIELD_PRODUCT_TYPE] = $this->getVariationLevelCode($product);
        $data[self::FIELD_ATTRIBUTES_IN_LEVEL] = array_keys($product->getRawValues());

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX === $format &&
            $data instanceof ProductInterface;
    }

    private function getVariationLevelCode(ProductInterface $product): string
    {
        if (!$product instanceof EntityWithFamilyVariantInterface) {
            return 'PimCatalogProduct';
        }

        $level = $product->getVariationLevel();
        switch ($level) {
            case 0:
                return 'PimCatalogRootProductModel';
            case 1:
                return 'PimCatalogSubProductModel';
            case 2:
                return 'PimCatalogVariantProduct';
            default:
                throw new \LogicException(sprintf('Invalid variant level. %s given', $level));
        }
    }
}

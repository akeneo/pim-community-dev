<?php

namespace Pim\Component\Catalog\Normalizer\Indexing;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Normalizer\Standard\ProductNormalizer as StandardProductNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize product to the indexing format.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelNormalizer implements NormalizerInterface
{
    public const INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX = 'indexing_product_and_model';

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

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && self::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX === $format;
    }
}

<?php

namespace Pim\Component\Catalog\Normalizer\Storage\Product;

use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Normalizer for a collection of product values
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValuesNormalizer implements NormalizerInterface
{
    /** @var ProductValueNormalizer */
    private $valueNormalizer;

    /**
     * @param ProductValueNormalizer $valueNormalizer
     */
    public function __construct(ProductValueNormalizer $valueNormalizer)
    {
        $this->valueNormalizer = $valueNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($values, $format = null, array $context = [])
    {
        $resultsToMerge = [];
        foreach ($values as $value) {
            $resultsToMerge[] = $this->valueNormalizer->normalize($value, $format, $context);
        }

        $result = array_replace_recursive(...$resultsToMerge);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'storage' === $format && $data instanceof ValueCollectionInterface;
    }
}

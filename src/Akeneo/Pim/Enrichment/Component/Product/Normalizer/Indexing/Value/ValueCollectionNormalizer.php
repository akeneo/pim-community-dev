<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollectionInterface as ValueCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\ProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductModel;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Normalizer for product value collection
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueCollectionNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function normalize($values, $format = null, array $context = [])
    {
        $normalizedValues = [];
        foreach ($values as $value) {
            $normalizedValues[] = $this->serializer->normalize($value, $format, $context);
        }

        $result = empty($normalizedValues) ? [] : array_replace_recursive(...$normalizedValues);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ValueCollectionInterface && (
                $format === ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX ||
                $format === ProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX ||
                $format === ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
            );
    }
}

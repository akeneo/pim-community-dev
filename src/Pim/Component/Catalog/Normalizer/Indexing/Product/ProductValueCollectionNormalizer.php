<?php

namespace Pim\Component\Catalog\Normalizer\Indexing\Product;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * Normalizer for product value collection
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueCollectionNormalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($values, $format = null, array $context = [])
    {
        $result = [];
        foreach ($values as $value) {
            if (AttributeTypes::IDENTIFIER !== $value->getAttribute()->getType()) {
                $normalizedValue = $this->serializer->normalize($value, $format, $context);
                $result = array_merge_recursive($result, $normalizedValue);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'indexing' === $format && $data instanceof ProductValueCollectionInterface;
    }
}

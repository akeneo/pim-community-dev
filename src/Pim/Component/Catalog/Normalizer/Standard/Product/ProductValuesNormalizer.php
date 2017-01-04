<?php

namespace Pim\Component\Catalog\Normalizer\Standard\Product;

use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * Normalizer for a collection of product values
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValuesNormalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($data, $format = null, array $context = [])
    {
        $result = [];

        foreach ($data as $value) {
            $normalizedValue = $this->serializer->normalize($value, $format, $context);
            if ($value instanceof ProductValueInterface) {
                $result[$value->getAttribute()->getCode()][] = $normalizedValue;
            } else {
                $result[] = $normalizedValue;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        $isCollection = $data instanceof Collection || is_array($data);
        $isStandardFormat = 'standard' === $format;

        if (!$isCollection || !$isStandardFormat) {
            return false;
        }

        $firstElementIsValue =
            (is_array($data) && empty($data)) ||
            ($data instanceof Collection && $data->isEmpty()) ||
            (is_array($data) && !empty($data) && $data[0] instanceof ProductValueInterface) ||
            ($data instanceof Collection && !$data->isEmpty() && $data->first() instanceof ProductValueInterface)
        ;

        return $firstElementIsValue;
    }
}

<?php

namespace Pim\Component\Catalog\Normalizer\Storage\Product;

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
            if (!$value instanceof ProductValueInterface) {
                throw new \InvalidArgumentException('This normalizer only handles "Pim\Component\Catalog\Model\ProductValueInterface".');
            }

            $stdValue = $this->serializer->normalize($value, 'standard', $context);

            $attribute = $value->getAttribute()->getCode();
            $channel = null !== $stdValue['scope'] ? $stdValue['scope'] : '<all_channels>';
            $locale = null !== $stdValue['locale'] ? $stdValue['locale'] : '<all_locales>';

            $result[$attribute][$channel][$locale] = $stdValue['data'];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Collection && 'storage' === $format;
    }
}

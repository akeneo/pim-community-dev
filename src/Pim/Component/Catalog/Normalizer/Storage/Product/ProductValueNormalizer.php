<?php

namespace Pim\Component\Catalog\Normalizer\Storage\Product;

use Pim\Component\Catalog\Model\ValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product value into an array
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $stdNormalizer;

    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->stdNormalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($value, $format = null, array $context = [])
    {
        $stdValue = $this->stdNormalizer->normalize($value, $format, $context);

        $attribute = $value->getAttribute()->getCode();
        $channel = null !== $stdValue['scope'] ? $stdValue['scope'] : '<all_channels>';
        $locale = null !== $stdValue['locale'] ? $stdValue['locale'] : '<all_locales>';

        $storageValue = [];
        $storageValue[$attribute][$channel][$locale] = $stdValue['data'];

        return $storageValue;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ValueInterface && 'storage' === $format;
    }
}

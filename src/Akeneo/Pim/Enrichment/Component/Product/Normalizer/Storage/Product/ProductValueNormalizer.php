<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product value into an array
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
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

        $attributeCode = $value->getAttributeCode();
        $channelCode = null !== $stdValue['scope'] ? $stdValue['scope'] : '<all_channels>';
        $localeCode = null !== $stdValue['locale'] ? $stdValue['locale'] : '<all_locales>';

        $storageValue = [];
        $storageValue[$attributeCode][$channelCode][$localeCode] = $stdValue['data'];

        return $storageValue;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ValueInterface && 'storage' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

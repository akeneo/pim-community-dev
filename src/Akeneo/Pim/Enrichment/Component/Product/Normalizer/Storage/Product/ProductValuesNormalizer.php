<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for a collection of product values
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValuesNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var NormalizerInterface */
    private $valueNormalizer;

    /**
     * @param NormalizerInterface $valueNormalizer
     */
    public function __construct(NormalizerInterface $valueNormalizer)
    {
        $this->valueNormalizer = $valueNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($values, $format = null, array $context = [])
    {
        $normalizedValues = [];
        foreach ($values as $value) {
            $normalizedValues[] = $this->valueNormalizer->normalize($value, $format, $context);
        }

        $result = empty($normalizedValues) ? [] : array_replace_recursive(...$normalizedValues);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'storage' === $format && $data instanceof WriteValueCollection;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

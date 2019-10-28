<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a DateTime as ISO-8601 (with the timezone)
 * See https://en.wikipedia.org/wiki/ISO_8601
 *
 * Example: 2016-06-13T12:07:58+00:00
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($date, $format = null, array $context = [])
    {
        if (null !== $date && $date instanceof \DateTimeInterface) {
            return $date->format('c');
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \DateTime && 'standard' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

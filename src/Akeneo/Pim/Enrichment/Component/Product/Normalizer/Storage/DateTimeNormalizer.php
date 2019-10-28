<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage;

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
    public function normalize($date, $format = null, array $context = [])
    {
        return $this->stdNormalizer->normalize($date, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \DateTime && 'storage' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

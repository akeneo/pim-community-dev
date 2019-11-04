<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a DateTime as ISO-8601 (with the timezone) to the indexing format.
 * See https://en.wikipedia.org/wiki/ISO_8601
 *
 * Example: 2017-06-13T12:07:58+00:00
 *
 * This format is based on the standard format.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var NormalizerInterface */
    protected $standardNormalizer;

    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->standardNormalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($date, $format = null, array $context = [])
    {
        return $this->standardNormalizer->normalize($date, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \DateTime && (
                ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX === $format
            );
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

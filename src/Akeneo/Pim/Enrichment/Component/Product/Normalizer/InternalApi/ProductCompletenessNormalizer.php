<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCompletenessNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * {@inheritdoc}
     *
     * @var ProductCompletenessWithMissingAttributeCodes $completeness
     */
    public function normalize($completeness, $format = null, array $context = [])
    {
        return [
            'required' => $completeness->requiredCount(),
            'missing'  => count($completeness->missingAttributeCodes()),
            'ratio'    => $completeness->ratio(),
            'locale'   => $completeness->localeCode(),
            'channel'  => $completeness->channelCode(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductCompletenessWithMissingAttributeCodes && $format === 'internal_api';
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

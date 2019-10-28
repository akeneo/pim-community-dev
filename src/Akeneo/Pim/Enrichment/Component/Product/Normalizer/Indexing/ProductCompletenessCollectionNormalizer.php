<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize the completeness collection to the indexing format.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCompletenessCollectionNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * {@inheritdoc}
     *
     * @var ProductCompletenessCollection $completenesses
     */
    public function normalize($completenesses, $format = null, array $context = [])
    {
        $data = [];

        foreach ($completenesses as $completeness) {
            $channelCode = $completeness->channelCode();
            $localeCode = $completeness->localeCode();
            $data[$channelCode][$localeCode] = $completeness->ratio();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return
            in_array($format, [
                ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
            ]) &&
            $data instanceof ProductCompletenessCollection;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

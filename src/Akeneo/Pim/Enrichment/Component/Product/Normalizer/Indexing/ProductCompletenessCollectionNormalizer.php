<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing;

use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\ProductNormalizer;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize the completeness collection to the indexing format.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCompletenessCollectionNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($completenesses, $format = null, array $context = [])
    {
        $data = [];

        foreach ($completenesses as $completeness) {
            /** @var ProductCompleteness $completeness */
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
                ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX,
                ProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX,
                ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
            ]) &&
            $data instanceof Collection &&
            !$data->isEmpty() &&
            $data->first() instanceof ProductCompleteness
        ;
    }
}

<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing;

use Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\ProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductModel;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize the completeness collection to the indexing format.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessCollectionNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($completenesses, $format = null, array $context = [])
    {
        $data = [];

        foreach ($completenesses as $completeness) {
            $channelCode = $completeness->getChannel()->getCode();
            $localeCode = $completeness->getLocale()->getCode();
            $data[$channelCode][$localeCode] = $completeness->getRatio();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return
            (
                ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX === $format ||
                ProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX === $format ||
                ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX === $format
            ) &&
            $data instanceof Collection &&
            !$data->isEmpty() &&
            $data->first() instanceof CompletenessInterface
        ;
    }
}

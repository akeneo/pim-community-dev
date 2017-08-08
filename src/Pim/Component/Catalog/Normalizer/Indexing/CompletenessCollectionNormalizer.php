<?php

namespace Pim\Component\Catalog\Normalizer\Indexing;

use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModelFormat\ProductModelNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductFormat\ProductNormalizer;
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
                ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX === $format
            ) &&
            $data instanceof Collection &&
            !$data->isEmpty() &&
            $data->first() instanceof CompletenessInterface
        ;
    }
}

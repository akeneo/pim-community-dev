<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValue;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for a price collection attribute
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PriceCollectionNormalizer extends AbstractProductValueNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof PriceCollectionValue && (
                $format === ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
            );
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getNormalizedData(ValueInterface $value)
    {
        $currencyIndexedPrices = [];
        $prices = $value->getData();

        if (null === $prices) {
            return null;
        }

        foreach ($prices as $price) {
            $currency = $price->getCurrency();
            if (null !== $currency && '' !== $currency) {
                $currencyIndexedPrices[$currency] = (string) $price->getData();
            }
        }

        return $currencyIndexedPrices;
    }
}

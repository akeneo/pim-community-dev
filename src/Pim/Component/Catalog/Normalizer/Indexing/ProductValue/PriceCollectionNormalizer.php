<?php

namespace Pim\Component\Catalog\Normalizer\Indexing\ProductValue;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\Product\ProductNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndModel\ProductModelNormalizer;
use Pim\Component\Catalog\Value\PriceCollectionValue;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for a price collection attribute
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PriceCollectionNormalizer extends AbstractProductValueNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof PriceCollectionValue &&
            AttributeTypes::BACKEND_TYPE_PRICE === $data->getAttribute()->getBackendType() && (
                $format === ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX ||
                $format === ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
            );
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

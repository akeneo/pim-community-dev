<?php

namespace Pim\Component\Catalog\Normalizer\Indexing\Value;

use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\Product\ProductNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Pim\Component\Catalog\Value\MetricValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for a metric product value
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MetricNormalizer extends AbstractProductValueNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof MetricValueInterface && (
                $format === ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX ||
                $format === ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function getNormalizedData(ValueInterface $value)
    {
        $productMetric = $value->getData();

        if (null !== $productMetric) {
            return [
                'data'      => (string)$productMetric->getData(),
                'base_data' => (string)$productMetric->getBaseData(),
                'unit'      => $productMetric->getUnit(),
                'base_unit' => $productMetric->getBaseUnit(),
            ];
        }

        return null;
    }
}

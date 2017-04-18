<?php

namespace Pim\Component\Catalog\Normalizer\Indexing\Product;

use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\ProductValue\MetricProductValueInterface;
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
        return $data instanceof MetricProductValueInterface && 'indexing' === $format;
    }

    /**
     * {@inheritdoc}
     */
    protected function getNormalizedData(ProductValueInterface $productValue)
    {
        $productMetric = $productValue->getData();

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

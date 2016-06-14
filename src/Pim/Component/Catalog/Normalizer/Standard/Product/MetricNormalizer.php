<?php

namespace Pim\Component\Catalog\Normalizer\Standard\Product;

use Pim\Component\Catalog\Model\MetricInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a metric entity into an array
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($metric, $format = null, array $context = [])
    {
        //TODO: when the Metric object is loaded from the database, the data is converted to a string
        //TODO: see http://doctrine-orm.readthedocs.io/projects/doctrine-dbal/en/latest/reference/types.html#decimal
        //TODO: at this point, $metric->getData() = '45.32165' or '56.000000'

        return [
            'data' => $metric->getData(),
            'unit' => $metric->getUnit() ?
                $metric->getUnit() :
                $metric->getValue()->getAttribute()->getDefaultMetricUnit(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof MetricInterface && 'standard' === $format;
    }
}

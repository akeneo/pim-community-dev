<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData;

use Pim\Component\Catalog\Model\MetricInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product metric to store it as mongodb_json
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = null;
        if ($object->getData() !== null) {
            $data = [
                'data'     => $object->getData(),
                'unit'     => $object->getUnit(),
                'baseData' => $object->getBaseData(),
                'baseUnit' => $object->getBaseUnit(),
                'family'   => $object->getFamily()
            ];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof MetricInterface && 'mongodb_json' === $format;
    }
}

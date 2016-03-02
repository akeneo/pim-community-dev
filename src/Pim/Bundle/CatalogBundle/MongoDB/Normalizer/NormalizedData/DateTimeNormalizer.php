<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Date time normalizer when normalizes a product value as mongodb_json
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return $object->getTimestamp();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \DateTime && 'mongodb_json' === $format;
    }
}

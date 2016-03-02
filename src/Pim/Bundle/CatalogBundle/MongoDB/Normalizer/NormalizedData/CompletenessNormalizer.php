<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData;

use Pim\Component\Catalog\Model\CompletenessInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Completeness normalizer
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $normalizedComp = [];
        $code = $object->getChannel()->getCode().'-'.$object->getLocale()->getCode();
        $normalizedComp[$code] = $object->getRatio();

        return $normalizedComp;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof CompletenessInterface && 'mongodb_json' === $format;
    }
}

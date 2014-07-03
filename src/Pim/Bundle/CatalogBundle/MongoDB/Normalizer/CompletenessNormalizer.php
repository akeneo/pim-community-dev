<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractCompleteness;

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
    public function normalize($object, $format = null, array $context = array())
    {
        $normalizedComp = array();
        $code = $object->getChannel()->getCode().'-'.$object->getLocale()->getCode();
        $normalizedComp[$code] = $object->getRatio();

        return $normalizedComp;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AbstractCompleteness && 'mongodb_json' === $format;
    }
}

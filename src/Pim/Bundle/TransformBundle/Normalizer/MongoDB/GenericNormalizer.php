<?php

namespace Pim\Bundle\TransformBundle\Normalizer\MongoDB;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a generic object into a simple id
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GenericNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return (ProductNormalizer::FORMAT === $format);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return $object->getId();
    }
}

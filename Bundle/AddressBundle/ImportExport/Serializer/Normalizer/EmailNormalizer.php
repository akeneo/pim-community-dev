<?php

namespace Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use Oro\Bundle\AddressBundle\Entity\AbstractEmail;

class EmailNormalizer implements NormalizerInterface, DenormalizerInterface
{
    const ABSTRACT_EMAIL_TYPE = 'Oro\Bundle\AddressBundle\Entity\AbstractEmail';

    /**
     * @param AbstractEmail $object
     * @param mixed $format
     * @param array $context
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return $object->getEmail();
    }

    /**
     * @param mixed $data
     * @param string $class
     * @param mixed $format
     * @param array $context
     * @return AbstractEmail
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        /** @var AbstractEmail $result */
        $result = new $class();
        $result->setEmail($data);
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AbstractEmail;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if (!is_string($data)) {
            return false;
        }
        return class_exists($type) && in_array(self::ABSTRACT_EMAIL_TYPE, class_parents($type));
    }
}

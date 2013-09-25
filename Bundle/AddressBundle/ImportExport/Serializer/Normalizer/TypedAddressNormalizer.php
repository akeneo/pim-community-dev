<?php

namespace Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;

class TypedAddressNormalizer implements DenormalizerInterface, NormalizerInterface, SerializerAwareInterface
{
    const ABSTRACT_TYPED_ADDRESS_TYPE = 'Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress';
    const TYPES_TYPE = 'ArrayCollection<Oro\Bundle\AddressBundle\Entity\AddressType>';

    /**
     * @var SerializerInterface|NormalizerInterface|DenormalizerInterface
     */
    protected $serializer;

    /**
     * @var AddressNormalizer
     */
    protected $addressNormalizer;

    public function __construct(AddressNormalizer $addressNormalizer)
    {
        $this->addressNormalizer = $addressNormalizer;
    }

    /**
     * @param SerializerInterface $serializer
     * @throws InvalidArgumentException
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        if (!$serializer instanceof NormalizerInterface || !$serializer instanceof DenormalizerInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'Serializer must implement "%s" and "%s"',
                    'Symfony\Component\Serializer\Normalizer\NormalizerInterface',
                    'Symfony\Component\Serializer\Normalizer\DenormalizerInterface'
                )
            );
        }
        $this->serializer = $serializer;
    }

    /**
     * @param AbstractTypedAddress $object
     * @param mixed $format
     * @param array $context
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $result = $this->addressNormalizer->normalize($object, $format, $context);
        $types = $object->getTypes();
        if (!$types->isEmpty()) {
            $result['types'] = $this->serializer->normalize($object->getTypes(), $format, $context);
        } else {
            $result['types'] = array();
        }
        return $result;
    }

    /**
     * @param mixed $data
     * @param string $class
     * @param mixed $format
     * @param array $context
     * @return AbstractTypedAddress
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        /** @var AbstractTypedAddress $result */
        $result = $this->addressNormalizer->denormalize($data, $class, $format, $context);

        if (!empty($data['types']) && is_array($data['types'])) {
            $types = $this->serializer->denormalize($data['types'], static::TYPES_TYPE, $format, $context);
            if ($types) {
                foreach ($types as $type) {
                    $result->addType($type);
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AbstractTypedAddress;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            is_array($data)
            && class_exists($type)
            && in_array(static::ABSTRACT_TYPED_ADDRESS_TYPE, class_parents($type));
    }
}

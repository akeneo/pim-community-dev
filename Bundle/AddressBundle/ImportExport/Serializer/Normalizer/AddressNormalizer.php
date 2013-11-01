<?php

namespace Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;

class AddressNormalizer implements NormalizerInterface, DenormalizerInterface
{
    const ABSTRACT_ADDRESS_TYPE = 'Oro\Bundle\AddressBundle\Entity\AbstractAddress';

    /**
     * @var array
     */
    protected $flatProperties = array(
        'label',
        'organization',
        'namePrefix',
        'firstName',
        'middleName',
        'lastName',
        'nameSuffix',
        'street',
        'street2',
        'postalCode',
        'city'
    );

    /**
     * @param AbstractAddress $object
     * @param mixed $format
     * @param array $context
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $normalizedData = array();
        foreach ($this->flatProperties as $property) {
            $normalizedData[$property] = $propertyAccessor->getValue($object, $property);
        }

        $normalizedData['regionText'] = $object->getRegionText();
        $normalizedData['region'] = $object->getRegion() ? $object->getRegion()->getCode() : null;
        $normalizedData['country'] = $object->getCountry() ? $object->getCountry()->getIso2Code() : null;

        return $normalizedData;
    }

    /**
     * @param mixed $data
     * @param string $class
     * @param mixed $format
     * @param array $context
     * @return AbstractAddress
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        /** @var AbstractAddress $result */
        $result = new $class();
        foreach ($this->flatProperties as $property) {
            if (!empty($data[$property])) {
                $propertyAccessor->setValue($result, $property, $data[$property]);
            }
        }
        $this->setRegionAndCountry($result, $data);

        return $result;
    }

    protected function setRegionAndCountry(AbstractAddress $address, array $data)
    {
        if (!empty($data['regionText'])) {
            $address->setRegionText($data['regionText']);
        }
        if (!empty($data['country'])) {
            $country = $this->createCountry($data['country']);
            $address->setCountry($country);
            if (!empty($data['region'])) {
                $address->setRegion($this->createRegion($data['region'], $country));
            }
        }
    }

    /**
     * @param string $iso2Code
     * @return Country
     */
    protected function createCountry($iso2Code)
    {
        return new Country($iso2Code);
    }

    /**
     * @param string $code
     * @param Country $country
     * @return Region
     */
    protected function createRegion($code, Country $country)
    {
        $result = new Region($country->getIso2Code() . '.' . $code);
        $result->setCode($code);
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AbstractAddress;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && class_exists($type) && in_array(self::ABSTRACT_ADDRESS_TYPE, class_parents($type));
    }
}

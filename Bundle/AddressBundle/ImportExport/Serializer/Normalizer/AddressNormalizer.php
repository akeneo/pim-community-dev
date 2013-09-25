<?php

namespace Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;

class AddressNormalizer implements NormalizerInterface, DenormalizerInterface
{
    const ABSTRACT_ADDRESS_TYPE = 'Oro\Bundle\AddressBundle\Entity\AbstractAddress';

    /**
     * @param AbstractAddress $object
     * @param mixed $format
     * @param array $context
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return array(
            'label' => $object->getLabel(),
            'firstName' => $object->getFirstName(),
            'lastName' => $object->getLastName(),
            'street' => $object->getStreet(),
            'street2' => $object->getStreet2(),
            'city' => $object->getCity(),
            'postalCode' => $object->getPostalCode() ? $object->getPostalCode() : null,
            'regionText' => $object->getRegionText(),
            'region' => $object->getRegion() ? $object->getRegion()->getCode() : null,
            'country' => $object->getCountry() ? $object->getCountry()->getIso2Code() : null,
        );
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
        /** @var AbstractAddress $result */
        $result = new $class();
        if (!empty($data['label'])) {
            $result->setLabel($data['label']);
        }
        if (!empty($data['firstName'])) {
            $result->setFirstName($data['firstName']);
        }
        if (!empty($data['lastName'])) {
            $result->setLastName($data['lastName']);
        }
        if (!empty($data['street'])) {
            $result->setStreet($data['street']);
        }
        if (!empty($data['street2'])) {
            $result->setStreet2($data['street2']);
        }
        if (!empty($data['city'])) {
            $result->setCity($data['city']);
        }
        if (!empty($data['postalCode'])) {
            $result->setPostalCode($data['postalCode']);
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

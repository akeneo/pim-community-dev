<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\AddressNormalizer;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;

class AddressNormalizerTest extends \PHPUnit_Framework_TestCase
{
    const ADDRESS_TYPE = 'Oro\Bundle\AddressBundle\Entity\Address';

    /**
     * @var AddressNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->normalizer = new AddressNormalizer();
    }

    public function testSupportsNormalization()
    {
        $this->assertFalse($this->normalizer->supportsNormalization(array()));
        $this->assertTrue(
            $this->normalizer->supportsNormalization(
                $this->getMock(AddressNormalizer::ABSTRACT_ADDRESS_TYPE)
            )
        );
        $this->assertTrue($this->normalizer->supportsNormalization($this->createAddress()));
    }

    public function testSupportsDenormalization()
    {
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), 'stdClass'));
        $this->assertFalse($this->normalizer->supportsDenormalization('string', self::ADDRESS_TYPE));
        $this->assertTrue($this->normalizer->supportsDenormalization(array(), self::ADDRESS_TYPE));
        $this->assertTrue(
            $this->normalizer->supportsDenormalization(
                array(),
                $this->getMockClass(AddressNormalizer::ABSTRACT_ADDRESS_TYPE)
            )
        );
    }

    /**
     * @dataProvider normalizeDataProvider
     *
     * @param AbstractAddress $object
     * @param array $expectedData
     */
    public function testNormalize(AbstractAddress $object, array $expectedData)
    {
        $this->assertEquals(
            $expectedData,
            $this->normalizer->normalize($object)
        );
    }

    /**
     * @dataProvider normalizeDataProvider
     *
     * @param AbstractAddress $object
     * @param array $data
     */
    public function testDenormalize(AbstractAddress $object, array $data)
    {
        $this->assertEquals(
            $object,
            $this->normalizer->denormalize($data, self::ADDRESS_TYPE)
        );
    }

    public function normalizeDataProvider()
    {
        return array(
            'without_country_and_region' => array(
                $this->createAddress()
                    ->setLabel('label')
                    ->setOrganization('Test Ltd.')
                    ->setNamePrefix('prefix')
                    ->setFirstName('first_name')
                    ->setMiddleName('middle_name')
                    ->setLastName('last_name')
                    ->setNameSuffix('suffix')
                    ->setStreet('street')
                    ->setStreet2('street2')
                    ->setCity('city')
                    ->setPostalCode('112233')
                    ->setRegionText('region_text')
                ,
                array(
                    'label' => 'label',
                    'organization' => 'Test Ltd.',
                    'namePrefix' => 'prefix',
                    'firstName' => 'first_name',
                    'middleName' => 'middle_name',
                    'lastName' => 'last_name',
                    'nameSuffix' => 'suffix',
                    'street' => 'street',
                    'street2' => 'street2',
                    'city' => 'city',
                    'postalCode' => '112233',
                    'regionText' => 'region_text',
                    'region' => null,
                    'country' => null,
                )
            ),
            'with_country_and_region' => array(
                $this->createAddress()
                    ->setRegion($this->createRegion('US.CA')->setCode('CA'))
                    ->setCountry($this->createCountry('US'))
                ,
                array(
                    'label' => null,
                    'organization' => null,
                    'namePrefix' => null,
                    'firstName' => null,
                    'middleName' => null,
                    'lastName' => null,
                    'nameSuffix' => null,
                    'street' => null,
                    'street2' => null,
                    'city' => null,
                    'postalCode' => null,
                    'regionText' => null,
                    'region' => 'CA',
                    'country' => 'US',
                )
            ),
            'with_country' => array(
                $this->createAddress()->setCountry($this->createCountry('US')),
                array(
                    'label' => null,
                    'organization' => null,
                    'namePrefix' => null,
                    'firstName' => null,
                    'middleName' => null,
                    'lastName' => null,
                    'nameSuffix' => null,
                    'street' => null,
                    'street2' => null,
                    'city' => null,
                    'postalCode' => null,
                    'regionText' => null,
                    'region' => null,
                    'country' => 'US',
                )
            )
        );
    }

    public function testDenormalizeRegionWithoutCountry()
    {
        $address = $this->normalizer->denormalize(
            array(
                'region' => $this->createRegion('US.CA')->setCode('CA')
            ),
            self::ADDRESS_TYPE
        );

        $this->assertInstanceOf(self::ADDRESS_TYPE, $address);
        $this->assertNull($address->getCountry());
        $this->assertNull($address->getRegion());
        $this->assertNull($address->getRegionText());
    }

    /**
     * @return Address
     */
    protected function createAddress()
    {
        $result = new Address();
        return $result;
    }

    /**
     * @param string $code
     * @return Country
     */
    protected function createCountry($code)
    {
        $result = new Country($code);
        return $result;
    }

    /**
     * @param string $combinedCode
     * @return Region
     */
    protected function createRegion($combinedCode)
    {
        $result = new Region($combinedCode);
        return $result;
    }
}

<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\AddressTypeNormalizer;
use Oro\Bundle\AddressBundle\Entity\AddressType;

class AddressTypeNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddressTypeNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->normalizer = new AddressTypeNormalizer();
    }

    public function testSupportsNormalization()
    {
        $this->assertFalse($this->normalizer->supportsNormalization(array()));
        $this->assertTrue($this->normalizer->supportsNormalization($this->createAddressType('shipping')));
    }

    public function testSupportsDenormalization()
    {
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), 'stdClass'));
        $this->assertFalse(
            $this->normalizer->supportsDenormalization(
                array(),
                AddressTypeNormalizer::ADDRESS_TYPE_TYPE
            )
        );
        $this->assertTrue(
            $this->normalizer->supportsDenormalization(
                'billing',
                AddressTypeNormalizer::ADDRESS_TYPE_TYPE
            )
        );
    }

    public function testNormalize()
    {
        $this->assertEquals(
            'shipping',
            $this->normalizer->normalize($this->createAddressType('shipping'), null)
        );
    }

    public function testDenormalize()
    {
        $this->assertEquals(
            $this->createAddressType('foo'),
            $this->normalizer->denormalize('foo', null)
        );
    }

    protected function createAddressType($name)
    {
        return new AddressType($name);
    }
}

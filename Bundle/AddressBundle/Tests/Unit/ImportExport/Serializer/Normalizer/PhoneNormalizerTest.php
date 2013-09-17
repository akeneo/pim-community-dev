<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\PhoneNormalizer;
use Oro\Bundle\AddressBundle\Tests\Unit\ImportExport\Serializer\Normalizer\Stub\StubPhone;

class PhoneNormalizerTest extends \PHPUnit_Framework_TestCase
{
    const PHONE_TYPE = 'Oro\Bundle\AddressBundle\Tests\Unit\ImportExport\Serializer\Normalizer\Stub\StubPhone';

    /**
     * @var PhoneNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->normalizer = new PhoneNormalizer();
    }

    public function testSupportsNormalization()
    {
        $this->assertFalse($this->normalizer->supportsNormalization(array()));
        $this->assertTrue($this->normalizer->supportsNormalization($this->createPhone()));
    }

    public function testSupportsDenormalization()
    {
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), 'stdClass'));
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), self::PHONE_TYPE));
        $this->assertFalse($this->normalizer->supportsDenormalization('phone', PhoneNormalizer::ABSTRACT_PHONE_TYPE));
        $this->assertTrue($this->normalizer->supportsDenormalization('phone', self::PHONE_TYPE));
    }

    public function testNormalize()
    {
        $this->assertEquals(
            'phone',
            $this->normalizer->normalize($this->createPhone()->setPhone('phone'), null)
        );
    }

    public function testDenormalize()
    {
        $result = $this->normalizer->denormalize('phone', self::PHONE_TYPE);

        $this->assertInstanceOf(self::PHONE_TYPE, $result);
        $this->assertEquals('phone', $result->getPhone());
    }

    protected function createPhone()
    {
        return new StubPhone();
    }
}

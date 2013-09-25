<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\AddressNormalizer;
use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\TypedAddressNormalizer;
use Oro\Bundle\AddressBundle\Tests\Unit\ImportExport\Serializer\Normalizer\Stub\StubTypedAddress;
use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;

class TypedAddressNormalizerTest extends \PHPUnit_Framework_TestCase
{
    const TYPED_ADDRESS_TYPE
        = 'Oro\Bundle\AddressBundle\Tests\Unit\ImportExport\Serializer\Normalizer\Stub\StubTypedAddress';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $serializer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $delegateNormalizer;

    /**
     * @var TypedAddressNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->delegateNormalizer =
            $this->getMockBuilder('Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\AddressNormalizer')
                ->setMethods(array('normalize', 'denormalize'))
                ->getMock();

        $this->serializer = $this->getMock('Oro\Bundle\ImportExportBundle\Serializer\Serializer');

        $this->normalizer = new TypedAddressNormalizer($this->delegateNormalizer);
        $this->normalizer->setSerializer($this->serializer);
    }

    /**
     * @expectedException \Symfony\Component\Serializer\Exception\InvalidArgumentException
     * @expectedExceptionMessage Serializer must implement
     */
    public function testSetInvalidSerialzer()
    {
        $this->normalizer->setSerializer($this->getMock('Symfony\Component\Serializer\SerializerInterface'));
    }

    public function testSupportsNormalization()
    {
        $this->assertFalse($this->normalizer->supportsNormalization(array()));
        $this->assertFalse(
            $this->normalizer->supportsNormalization(
                $this->getMock(AddressNormalizer::ABSTRACT_ADDRESS_TYPE)
            )
        );
        $this->assertTrue($this->normalizer->supportsNormalization(new StubTypedAddress()));
    }

    public function testSupportsDenormalization()
    {
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), 'stdClass'));
        $this->assertFalse(
            $this->normalizer->supportsDenormalization(
                'string',
                $this->getMock(TypedAddressNormalizer::ABSTRACT_TYPED_ADDRESS_TYPE)
            )
        );
        $this->assertFalse(
            $this->normalizer->supportsDenormalization(
                array(),
                TypedAddressNormalizer::ABSTRACT_TYPED_ADDRESS_TYPE
            )
        );
        $this->assertTrue(
            $this->normalizer->supportsDenormalization(array(), self::TYPED_ADDRESS_TYPE)
        );
    }

    public function testNormalizeWithoutTypes()
    {
        $object = $this->createTypedAddress();
        $format = null;
        $context = array('context');

        $delegateData = array('label' => 'Label');

        $this->delegateNormalizer->expects($this->once())
            ->method('normalize')
            ->with($object, $format, $context)
            ->will($this->returnValue($delegateData));

        $this->serializer->expects($this->never())->method($this->anything());

        $this->assertEquals(
            array('label' => 'Label', 'types' => array()),
            $this->normalizer->normalize($object, $format, $context)
        );
    }

    public function testNormalizeWithTypes()
    {
        $object = $this->createTypedAddress();
        $object->addType(new AddressType('billing'));
        $format = null;
        $context = array('context');

        $delegateData = array('label' => 'Label');

        $this->delegateNormalizer->expects($this->once())
            ->method('normalize')
            ->with($object, $format, $context)
            ->will($this->returnValue($delegateData));

        $this->serializer->expects($this->once())->method('normalize')
            ->with($object->getTypes(), $format, $context)
            ->will($this->returnValue(array('billing')));

        $this->assertEquals(
            array('label' => 'Label', 'types' => array('billing')),
            $this->normalizer->normalize($object, $format, $context)
        );
    }

    public function testDenormalizeWithoutTypes()
    {
        $data = array('label' => 'Label', 'types' => array());
        $expectedObject = $this->createTypedAddress()->setLabel('Label');
        $format = null;
        $context = array('context');

        $this->delegateNormalizer->expects($this->once())
            ->method('denormalize')
            ->with($data, self::TYPED_ADDRESS_TYPE, $format, $context)
            ->will($this->returnValue($expectedObject));

        $this->serializer->expects($this->never())->method($this->anything());

        $this->assertEquals(
            $expectedObject,
            $this->normalizer->denormalize($data, self::TYPED_ADDRESS_TYPE, $format, $context)
        );
    }

    public function testDenormalizeWithTypes()
    {
        $data = array('label' => 'Label', 'types' => array('billing', 'shipping'));
        $expectedObject = $this->createTypedAddress()->setLabel('Label');
        $format = null;
        $context = array('context');

        $this->delegateNormalizer->expects($this->once())
            ->method('denormalize')
            ->with($data, self::TYPED_ADDRESS_TYPE, $format, $context)
            ->will($this->returnValue($expectedObject));

        $expectedTypes = new ArrayCollection(
            array(
                new AddressType('billing'),
                new AddressType('shipping'),
            )
        );

        $this->serializer->expects($this->once())
            ->method('denormalize')
            ->with(
                array('billing', 'shipping'),
                TypedAddressNormalizer::TYPES_TYPE,
                $format,
                $context
            )
            ->will($this->returnValue($expectedTypes));

        $this->assertSame(
            $expectedObject,
            $this->normalizer->denormalize($data, self::TYPED_ADDRESS_TYPE, $format, $context)
        );

        $this->assertEquals($expectedTypes, $expectedObject->getTypes());
    }

    /**
     * @return AbstractTypedAddress
     */
    protected function createTypedAddress()
    {
        return new StubTypedAddress();
    }
}

<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\Form\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Form\DataTransformer\AddressTypeToTypeTransformer;

class AddressTypeToTypeTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var AddressTypeToTypeTransformer
     */
    protected $transformer;

    protected function setUp()
    {
        $this->markTestIncomplete('Should be fixed in scope of CRM-221');

        $this->om = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->transformer = new AddressTypeToTypeTransformer($this->om);
    }

    /**
     * @dataProvider typesDataProvider
     * @param null|AddressType $type
     * @param string $expected
     */
    public function testTransform($type, $expected)
    {
        $this->assertEquals($expected, $this->transformer->transform($type));
    }

    /**
     * @return array
     */
    public function typesDataProvider()
    {
        return array(
            array(null, ''),
            array($this->getAddressTypeMock('test'), 'test')
        );
    }

    public function testReverseTransformEmpty()
    {
        $this->assertNull($this->transformer->reverseTransform(false));
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\TransformationFailedException
     * @expectedExceptionMessage An address type with type "unknown" does not exist!
     */
    public function testReverseTransformException()
    {
        $type = 'unknown';
        $this->assertRepositoryCall($type, null);
        $this->transformer->reverseTransform($type);
    }

    public function testReverseTransform()
    {
        $type = 'test';
        $addressType = $addressType = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\AddressType')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertRepositoryCall($type, $addressType);
        $this->assertSame($addressType, $this->transformer->reverseTransform($type));
    }

    protected function assertRepositoryCall($type, $addressType)
    {
        $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(array('type' => $type))
            ->will($this->returnValue($addressType));

        $this->om->expects($this->once())
            ->method('getRepository')
            ->with('OroAddressBundle:AddressType')
            ->will($this->returnValue($repository));
    }

    protected function getAddressTypeMock($type)
    {
        $addressType = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\AddressType')
            ->disableOriginalConstructor()
            ->getMock();
        $addressType->expects($this->once())
            ->method('getType')
            ->will($this->returnValue($type));
        return $addressType;
    }
}

<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Transformer\Property;

use Pim\Bundle\TransformBundle\Transformer\Property\NestedTranslationTransformer;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NestedTranslationTransformerTest extends \PHPUnit_Framework_TestCase
{
    protected $propertyAccessor;
    protected $transformer;

    protected function setUp()
    {
        $this->propertyAccessor = $this->getMock('Symfony\Component\PropertyAccess\PropertyAccessorInterface');
        $this->transformer = new NestedTranslationTransformer($this->propertyAccessor);
    }

    public function testTransform()
    {
        $data = array('data');
        $result = $this->transformer->transform($data);
        $this->assertEquals($result, $data);
    }

    /**
     * @expectedException \Pim\Bundle\TransformBundle\Exception\PropertyTransformerException
     * @expectedExceptionMessage Data should be an array
     */
    public function testTransformNotArray()
    {
        $this->transformer->transform('scalar');
    }

    public function testSetValue()
    {
        $object = $this->getMockBuilder('stdClass')
            ->setMethods(array('setLocale'))
            ->getMock();

        $object->expects($this->at(0))
            ->method('setLocale')
            ->with($this->equalTo('locale1'));
        $this->propertyAccessor->expects($this->at(0))
            ->method('setValue')
            ->with($this->identicalTo($object), $this->equalTo('translation.key'), 'value1');

        $object->expects($this->at(1))
            ->method('setLocale')
            ->with($this->equalTo('locale2'));
        $this->propertyAccessor->expects($this->at(1))
            ->method('setValue')
            ->with($this->identicalTo($object), $this->equalTo('translation.key'), 'value2');

        $columnInfo = $this->getMock('Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface');
        $this->transformer->setValue(
            $object,
            $columnInfo,
            array('locale1' => 'value1', 'locale2' => 'value2'),
            array('propertyPath' => 'key')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage propertyPath option is required
     */
    public function testNoPropertyPath()
    {
        $columnInfo = $this->getMock('Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface');
        $this->transformer->setValue(new \stdClass, $columnInfo, array());
    }
}

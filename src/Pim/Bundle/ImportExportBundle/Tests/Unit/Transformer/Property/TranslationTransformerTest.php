<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer\Property;

use Pim\Bundle\ImportExportBundle\Transformer\Property\TranslationTransformer;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testSetValue()
    {
        $column = $this->getMock('Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoInterface');
        $column->expects($this->any())
            ->method('getSuffixes')
            ->will($this->returnValue(['locale']));
        $column->expects($this->any())
            ->method('getPropertyPath')
            ->will($this->returnValue('property_path'));
        $object = $this->getMockBuilder('stdClass')
            ->setMethods(['setLocale'])
            ->getMock();
        $object->expects($this->once())
            ->method('setLocale')
            ->with($this->equalTo('locale'));
        $propertyAccessor = $this->getMock('Symfony\Component\PropertyAccess\PropertyAccessorInterface');
        $propertyAccessor->expects($this->once())
            ->method('setValue')
            ->with(
                $this->identicalTo($object),
                $this->equalTo('translation.property_path'),
                $this->equalTo('value')
            );
        $transformer = new TranslationTransformer($propertyAccessor);
        $transformer->setValue($object, $column, 'value');
    }

    public function testSetValueWithLocale()
    {
        $column = $this->getMock('Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoInterface');
        $column->expects($this->any())
            ->method('getSuffixes')
            ->will($this->returnValue([]));
        $column->expects($this->any())
            ->method('getLocale')
            ->will($this->returnValue('locale'));
        $column->expects($this->any())
            ->method('getPropertyPath')
            ->will($this->returnValue('property_path'));
        $object = $this->getMockBuilder('stdClass')
            ->setMethods(['setLocale'])
            ->getMock();
        $object->expects($this->once())
            ->method('setLocale')
            ->with($this->equalTo('locale'));
        $propertyAccessor = $this->getMock('Symfony\Component\PropertyAccess\PropertyAccessorInterface');
        $propertyAccessor->expects($this->once())
            ->method('setValue')
            ->with(
                $this->identicalTo($object),
                $this->equalTo('translation.property_path'),
                $this->equalTo('value')
            );
        $transformer = new TranslationTransformer($propertyAccessor);
        $transformer->setValue($object, $column, 'value');
    }
}

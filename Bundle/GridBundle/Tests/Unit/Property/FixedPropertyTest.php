<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Property;

use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;
use Oro\Bundle\GridBundle\Property\FixedProperty;

class FixedPropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $property = new FixedProperty('name');
        $this->assertEquals('name', $property->getName());
    }

    public function testGetValueByName()
    {
        $property = new FixedProperty('name');
        $value = 'value';

        $record = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface');
        $record->expects($this->once())->method('getValue')->with('name')->will($this->returnValue($value));
        $this->assertEquals($value, $property->getValue($record));
    }

    public function testGetValueByKey()
    {
        $property = new FixedProperty('name', 'key');
        $value = 'value';

        $record = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface');
        $record->expects($this->once())->method('getValue')->with('key')->will($this->returnValue($value));
        $this->assertEquals($value, $property->getValue($record));
    }
}

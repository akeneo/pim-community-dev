<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\ResultRecord;
use Oro\Bundle\GridBundle\Tests\Unit\Datagrid\Stub\StubEntity;

class ResultRecordTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getValueDataProvider
     * @param mixed $data
     * @param string $propertyName
     * @param mixed $expectedValue
     */
    public function testGetValue($data, $propertyName, $expectedValue)
    {
        $object = new ResultRecord($data);
        $this->assertEquals($expectedValue, $object->getValue($propertyName));
    }

    /**
     * @return array
     */
    public function getValueDataProvider()
    {
        return array(
            'read array property' => array(
                'data' => array('foo' => 'bar'),
                'foo',
                'bar'
            ),
            'read array null property' => array(
                'data' => array('foo' => null),
                'foo',
                null
            ),
            'call get method' => array(
                'data' => new StubEntity('value'),
                'privateProperty',
                'value'
            ),
            'get property value' => array(
                'data' => new StubEntity(null, 'value'),
                'publicProperty',
                'value'
            ),
            'call is method' => array(
                'data' => new StubEntity(null, null, true),
                'booleanProperty',
                true
            ),
            'get array property from mixed data' => array(
                'data' => array(new StubEntity(), 'foo' => 'bar'),
                'foo',
                'bar'
            ),
            'call object get method from mixed data' => array(
                'data' => array(new StubEntity('value'), 'privateProperty' => 'foo'),
                'privateProperty',
                'value'
            ),
        );
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unable to retrieve the value of "foo" property
     */
    public function testGetValueFails()
    {
        $object = new ResultRecord(array());
        $object->getValue('foo');
    }
}

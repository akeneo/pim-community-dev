<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Property;

use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;
use Oro\Bundle\GridBundle\Property\ActionConfigurationProperty;

class ActionConfigurationPropertyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that property returns always array and has correct name
     *
     * @dataProvider callbackProvider
     */
    public function testProperty(\Closure $callback)
    {
        $property = new ActionConfigurationProperty($callback);

        $recordMock = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface');
        $this->assertInternalType('array', $property->getValue($recordMock));
        $this->assertEquals('action_configuration', $property->getName());
    }

    /**
     * @return array
     */
    public function callbackProvider()
    {
        return array(
            'callback returns array' => array(
                'callback' => function (ResultRecordInterface $record) {
                    return array();
                }
            ),
            'callback returns something else' => array(
                'callback' => function (ResultRecordInterface $record) {
                    return false;
                }
            )
        );
    }
}

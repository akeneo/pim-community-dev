<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Property;

use Oro\Bundle\GridBundle\Property\CallbackProperty;

class CallbackPropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testGetValue()
    {
        $callback = function ($rec) {
            return $rec;
        };

        $record = $this->getMock('Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface');

        $callbackProperty = new CallbackProperty('test', $callback);
        $res = $callbackProperty->getValue($record);

        $this->assertEquals($res, $record);
    }
}

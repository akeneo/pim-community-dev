<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

use Oro\Bundle\GridBundle\Action\MassAction\MassActionResponse;

class MassActionResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $response = new MassActionResponse(true, 'test');

        $this->assertEquals($response->getMessage(), 'test');
        $this->assertCount(0, $response->getOptions());
        $this->assertNull($response->getOption('test'));
    }
}

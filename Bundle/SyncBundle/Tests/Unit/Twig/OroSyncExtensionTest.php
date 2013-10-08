<?php

namespace Oro\Bundle\SyncBundle\Tests\Unit\Twig;

use Oro\Bundle\SyncBundle\Twig\OroSyncExtension;

class OroSyncExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $extension;

    public function setUp()
    {
        $topicPublisher = $this->getMock('Oro\Bundle\SyncBundle\Wamp\TopicPublisher');
        $this->extension = new OroSyncExtension($topicPublisher);
    }

    public function tearDown()
    {
        unset($this->extension);
    }
    public function testGetName()
    {
        $this->assertEquals('sync_extension', $this->extension->getName());
    }

    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(1, $functions);
        $function = reset($functions);
        $this->assertEquals('check_ws', $function->getName());
    }
}

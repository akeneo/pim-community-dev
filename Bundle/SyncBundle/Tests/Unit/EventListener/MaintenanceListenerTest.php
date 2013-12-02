<?php
namespace Oro\Bundle\SyncBundle\Tests\Unit\EventListener;

use Oro\Bundle\SyncBundle\EventListener\MaintenanceListener;

class MaintenanceListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $topicPublisher;

    protected function setUp()
    {
        $this->topicPublisher = $this->getMockBuilder('Oro\Bundle\SyncBundle\Wamp\TopicPublisher')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown()
    {
        unset($this->topicPublisher);
    }

    public function testOnModeOn()
    {
        $this->topicPublisher
            ->expects($this->once())
            ->method('send')
            ->with('oro/maintenance', array('isOn' => true, 'msg' => 'Maintenance mode is ON'));
        /** @var MaintenanceListener $publisher */
        $publisher = new MaintenanceListener($this->topicPublisher);
        $publisher->onModeOn();
    }

    public function testOnModeOff()
    {
        $this->topicPublisher
            ->expects($this->once())
            ->method('send')
            ->with('oro/maintenance', array('isOn' => false, 'msg' => 'Maintenance mode is OFF'));
        /** @var MaintenanceListener $publisher */
        $publisher = new MaintenanceListener($this->topicPublisher);
        $publisher->onModeOff();
    }
}

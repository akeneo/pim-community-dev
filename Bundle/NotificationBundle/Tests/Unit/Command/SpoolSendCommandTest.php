<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Command;

use Oro\Bundle\NotificationBundle\Command\SpoolSendCommand;

class SpoolSendCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SpoolSendCommand
     */
    private $command;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    public function setUp()
    {
        $this->command = new SpoolSendCommand();

        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->command->setContainer($this->container);
    }

    public function testConfiguration()
    {
        $this->assertNotEmpty($this->command->getDescription());
        $this->assertEquals('oro:spool:send', $this->command->getName());
    }

    /**
     * Test execute
     */
    public function testExecute()
    {
        $mailer = $this->getMockBuilder('\Swift_Mailer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->container->expects($this->at(0))
            ->method('get')
            ->with('oro_notification.mailer')
            ->will($this->returnValue($mailer));

        $this->container->expects($this->once())
            ->method('set')
            ->with('mailer', $mailer)
            ->will($this->returnSelf());

        $this->container->expects($this->at(2))
            ->method('get')
            ->with('mailer')
            ->will($this->returnValue($mailer));

        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $this->command->run($input, $output);
    }
}

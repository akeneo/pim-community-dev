<?php

namespace Oro\Bundle\CronBundle\Entity;

class ScheduleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Schedule
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new Schedule();
    }

    public function testGetId()
    {
        $this->assertNull($this->object->getId());
    }

    public function testCommand()
    {
        $object  = $this->object;
        $command = 'oro:test';

        $this->assertEmpty($object->getCommand());

        $object->setCommand($command);

        $this->assertEquals($command, $object->getCommand());
    }

    public function testDefinition()
    {
        $object = $this->object;
        $def    = '*/5 * * * *';

        $this->assertEmpty($object->getDefinition());

        $object->setDefinition($def);

        $this->assertEquals($def, $object->getDefinition());
    }
}

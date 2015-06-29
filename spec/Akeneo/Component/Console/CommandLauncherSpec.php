<?php

namespace spec\Akeneo\Component\Console;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CommandLauncherSpec extends ObjectBehavior
{
    public function let()
    {
        $appRoot = realpath(__DIR__ . '/../../../../app');
        $this->beConstructedWith($appRoot, 'test');
    }

    public function it_can_be_initialized()
    {
        $this->shouldHaveType('Akeneo\Component\Console\CommandLauncher');
    }

    public function it_can_execute_in_background()
    {
        $command = 'pim:foobar';

        $this->executeBackground($command)->shouldReturn(null);
    }

    public function it_can_execute_in_foreground()
    {
        $command = 'pim:foobar';

        $result = $this->executeForeground($command);

        $result->shouldImplement('Akeneo\Component\Console\CommandResultInterface');
    }
}

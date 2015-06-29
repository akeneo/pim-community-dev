<?php

namespace spec\Akeneo\Component\Console;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CommandLauncherSpec extends ObjectBehavior
{
    function let()
    {
        $appRoot = realpath(__DIR__ . '/../../../../app');
        $this->beConstructedWith($appRoot, 'test');
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType('Akeneo\Component\Console\CommandLauncher');
    }
}

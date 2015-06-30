<?php

namespace spec\Akeneo\Component\Console;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CommandResultSpec extends ObjectBehavior
{
    /** @var array */
    protected $ouput = [
        'foo' => 'bar',
        'baz' => 'snafu'
    ];

    public function let()
    {
        $this->beConstructedWith($this->ouput, 0);
    }

    public function it_can_be_initialized()
    {
        $this->shouldHaveType('Akeneo\Component\Console\CommandResult');
        $this->shouldImplement('Akeneo\Component\Console\CommandResultInterface');
    }

    public function it_can_return_command_output()
    {
        $this->getCommandOutput()->shouldReturn($this->ouput);
    }

    public function it_can_return_command_status()
    {
        $this->getCommandStatus()->shouldReturn(0);
    }
}

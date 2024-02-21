<?php

namespace spec\Akeneo\Tool\Component\Console;

use Akeneo\Tool\Component\Console\CommandResult;
use Akeneo\Tool\Component\Console\CommandResultInterface;
use PhpSpec\ObjectBehavior;

class CommandResultSpec extends ObjectBehavior
{
    /** @var array */
    protected $ouput = [
        'foo' => 'bar',
        'baz' => 'snafu'
    ];

    function let()
    {
        $this->beConstructedWith($this->ouput, 0);
    }

    public function it_can_be_initialized()
    {
        $this->shouldHaveType(CommandResult::class);
        $this->shouldImplement(CommandResultInterface::class);
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

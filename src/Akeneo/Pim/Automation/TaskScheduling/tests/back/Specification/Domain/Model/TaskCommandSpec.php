<?php

namespace Specification\Akeneo\Pim\Automation\TaskScheduling\Domain\Model;

use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskCommand;
use PhpSpec\ObjectBehavior;

class TaskCommandSpec extends ObjectBehavior
{
    function it_can_be_created_with_a_valid_command()
    {
        $this->beConstructedThrough('fromString', ['bin/console list']);
        $this->asString()->shouldBe('bin/console list');
    }

    function it_cannot_be_created_with_an_empty_command()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(new \InvalidArgumentException('Command should not be empty'))->duringInstantiation();
    }

    function it_can_compare_itself_to_another_command()
    {
        $this->beConstructedThrough('fromString', ['bin/console list']);
        $this->equals(TaskCommand::fromString('bin/console list'))->shouldBe(true);
        $this->equals(TaskCommand::fromString('bin/console help list'))->shouldBe(false);
    }
}

<?php

namespace Specification\Akeneo\Pim\Automation\TaskScheduling\Domain\Model;

use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskSchedule;
use PhpSpec\ObjectBehavior;

class TaskScheduleSpec extends ObjectBehavior
{
    function it_can_be_created_with_a_valid_expression()
    {
        $this->beConstructedThrough('fromString', ['* * * * *']);
        $this->shouldBeAnInstanceOf(TaskSchedule::class);
        $this->asString()->shouldBe('* * * * *');
    }

    function it_cannot_be_constructed_with_an_empty_string()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(new \InvalidArgumentException('Schedule should not be empty'))->duringInstantiation();
    }
}

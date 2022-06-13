<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\MigrateToUuidStep;
use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\LogContext;
use PhpSpec\ObjectBehavior;

class LogContextSpec extends ObjectBehavior
{
    public function let(MigrateToUuidStep $step)
    {
        $step->getName()->willReturn('myStepName');
        $step->getStatus()->willReturn('myStepStatus');
        $step->getDuration()->willReturn(5.123);

        $this->beConstructedWith($step);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(LogContext::class);
    }

    function it_has_default_data_in_context()
    {
        $this->toArray()->shouldReturn(['step' => 'myStepName', 'step_status' => 'myStepStatus', 'step_duration' => 5.123]);
    }

    function it_adds_data_in_context()
    {
        $this->addContext('newElement', 'value1');
        $this->addContext('anotherElement', 'value2');
        $this->toArray()
            ->shouldReturn(['newElement' => 'value1', 'anotherElement' =>'value2', 'step' => 'myStepName', 'step_status' => 'myStepStatus', 'step_duration' => 5.123]);
    }

    function it_displays_extra_values_but_does_not_add_them_in_content()
    {
        $this->toArray(['extraElement' => 'value'])
            ->shouldReturn(['extraElement' => 'value', 'step' => 'myStepName', 'step_status' => 'myStepStatus', 'step_duration' => 5.123]);
        $this->toArray()->shouldReturn(['step' => 'myStepName', 'step_status' => 'myStepStatus', 'step_duration' => 5.123]);
    }

    function it_cannot_override_default_context_keys()
    {
        $this->addContext('step', 'overridenStepName');
        $this->addContext('step_status', 'overridenStepStatus');
        $this->addContext('step_duration', 13.987);
        $this->toArray()
            ->shouldReturn(['step' => 'myStepName', 'step_status' => 'myStepStatus', 'step_duration' => 5.123]);
        $this->toArray(['step' => 'overridenStepName', 'step_status' => 'overridenStepStatus', 'step_duration' => 13.987])
            ->shouldReturn(['step' => 'myStepName', 'step_status' => 'myStepStatus', 'step_duration' => 5.123]);
    }
}

<?php

namespace spec\Akeneo\Component\Batch\Model;

use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class WarningSpec extends ObjectBehavior
{
    function let(StepExecution $stepExecution)
    {
        $this->beConstructedWith(
            $stepExecution,
            'my name',
            'my reason',
            ['myparam' => 'mavalue'],
            ['myitem' => 'myvalue']
        );
    }

    function it_provides_a_step_execution($stepExecution)
    {
        $this->getStepExecution()->shouldReturn($stepExecution);
    }

    function it_provides_array_format()
    {
        $this->toArray()->shouldReturn(
            [
                'name'   => 'my name',
                'reason' => 'my reason',
                'reasonParameters' => [
                    'myparam' => 'mavalue'
                ],
                'item' => [
                    'myitem' => 'myvalue'
                ]
            ]
        );
    }
}

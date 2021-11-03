<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\Processor\Normalization;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\DTO\SelectOptionDetails;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\Processor\Normalization\SelectOptionDetailsProcessor;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use PhpSpec\ObjectBehavior;

class SelectOptionDetailsProcessorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldImplement(ItemProcessorInterface::class);
        $this->shouldHaveType(SelectOptionDetailsProcessor::class);
    }

    function it_processes_a_select_option_details()
    {
        $option = new SelectOptionDetails(
            'nutrition',
            'ingredient',
            'salt',
            ['en_US' => 'Salt', 'fr_FR' => 'Sel']
        );

        $this->process($option)->shouldReturn(
            [
                'attribute' => 'nutrition',
                'column' => 'ingredient',
                'code' => 'salt',
                'labels' => ['en_US' => 'Salt', 'fr_FR' => 'Sel'],
            ]
        );
    }

    function it_throws_an_exception_when_processing_anything_but_a_select_option_details()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('process', [new \stdClass()]);
    }
}

<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\Reader\Database;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\DTO\SelectOptionDetails;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\CountSelectOptions;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\GetSelectOptionDetails;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\Reader\Database\SelectOptionDetailsReader;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use PhpSpec\ObjectBehavior;

class SelectOptionDetailsReaderSpec extends ObjectBehavior
{
    function let(
        CountSelectOptions $countSelectOptions,
        GetSelectOptionDetails $getSelectOptionDetails,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($countSelectOptions, $getSelectOptionDetails);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_select_option_reader()
    {
        $this->shouldImplement(ItemReaderInterface::class);
        $this->shouldImplement(TrackableItemReaderInterface::class);
        $this->shouldImplement(InitializableInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);

        $this->shouldHaveType(SelectOptionDetailsReader::class);
    }

    function it_tracks_the_expected_total_count_of_read_items(CountSelectOptions $countSelectOptions)
    {
        $countSelectOptions->all()->willReturn(42);

        $this->totalItems()->shouldReturn(42);
    }

    function it_reads_select_options(GetSelectOptionDetails $getSelectOptionDetails, StepExecution $stepExecution)
    {
        $firstOption = new SelectOptionDetails('foo', 'bar', 'baz', ['en_US' => 'BAZ']);
        $secondOption = new SelectOptionDetails('bar', 'baz', 'bam', []);
        $getSelectOptionDetails->__invoke()->shouldBeCalled()->willYield([$firstOption, $secondOption]);

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(2);

        $this->initialize();
        $this->read()->shouldReturn($firstOption);
        $this->read()->shouldReturn($secondOption);
        $this->read()->shouldReturn(null);
    }
}

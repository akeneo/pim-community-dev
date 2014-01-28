<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Reader\ORM;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\AbstractQuery;
use Oro\Bundle\BatchBundle\Entity\StepExecution;

class ReaderSpec extends ObjectBehavior
{
    function let(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_reader()
    {
        $this->shouldBeAnInstanceOf('Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
        $this->shouldBeAnInstanceOf('Oro\Bundle\BatchBundle\Item\ItemReaderInterface');
        $this->shouldBeAnInstanceOf('Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_reads_records_one_by_one(AbstractQuery $query)
    {
        $query->execute()->willReturn(array('foo','bar'));

        $this->setQuery($query);
        $this->read()->shouldReturn('foo');
        $this->read()->shouldReturn('bar');
        $this->read()->shouldReturn(null);
    }

    function it_increments_read_count_for_each_record_reading($stepExecution, AbstractQuery $query)
    {
        $query->execute()->willReturn(array('foo','bar'));

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(2);

        $this->setQuery($query);
        $this->read();
        $this->read();
        $this->read();
    }

    function it_does_not_expose_any_configuration_fields()
    {
        $this->getConfigurationFields()->shouldHaveCount(0);
    }
}

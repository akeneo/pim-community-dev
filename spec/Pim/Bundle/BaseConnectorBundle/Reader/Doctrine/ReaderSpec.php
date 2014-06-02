<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Reader\Doctrine;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\AbstractQuery;
use Doctrine\MongoDB\Query\Query;
use Doctrine\MongoDB\Cursor;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

/**
 * @require Doctrine\MongoDB\Query\Query
 * @require Doctrine\MongoDB\Cursor
 */
class ReaderSpec extends ObjectBehavior
{
    function let(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_reader()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_can_have_an_orm_query(AbstractQuery $query)
    {
        $this->setQuery($query);
        $this->getQuery()->shouldReturn($query);
    }

    function it_can_have_a_mongodb_query(Query $query)
    {
        $this->setQuery($query);
        $this->getQuery()->shouldReturn($query);
    }

    function it_cannot_have_another_type_of_query()
    {
        $this->shouldThrow('\InvalidArgumentException')->duringSetQuery('foo');
    }

    function it_reads_orm_records_one_by_one(AbstractQuery $query)
    {
        $query->execute()->willReturn(array('foo','bar'));

        $this->setQuery($query);
        $this->read()->shouldReturn('foo');
        $this->read()->shouldReturn('bar');
        $this->read()->shouldReturn(null);
    }

    function it_reads_mongodb_records_one_by_one(Query $query, Cursor $cursor)
    {
        $query->execute()->willReturn($cursor);
        $result = ['foo', 'bar'];

        $cursor->getNext()->shouldBeCalled();
        $cursor->current()->willReturn(array_shift($result));
        $cursor->next()->will(function () use ($cursor, &$result) {
            $cursor->current()->willReturn(array_shift($result));
        });

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

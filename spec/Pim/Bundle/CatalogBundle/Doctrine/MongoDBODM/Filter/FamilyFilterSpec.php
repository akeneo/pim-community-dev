<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class FamilyFilterSpec extends ObjectBehavior
{
    function let(Builder $qb, ObjectIdResolverInterface $objectIdResolver)
    {
        $this->beConstructedWith(
            $objectIdResolver,
            ['family.id', 'family.code'],
            ['IN', 'NOT IN', 'EMPTY', 'NOT EMPTY']
        );
        $this->setQueryBuilder($qb);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\FieldFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['IN', 'NOT IN', 'EMPTY', 'NOT EMPTY']);
        $this->supportsOperator('IN')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_an_in_filter_on_an_id_field_in_the_query($qb)
    {
        $qb->field('family')
            ->shouldBeCalled()
            ->willReturn($qb);
        $qb->in([12, 13])->shouldBeCalled();

        $this->addFieldFilter('family.id', 'IN', [12, 13]);
    }

    function it_adds_an_in_filter_on_a_code_field_in_the_query($qb, $objectIdResolver)
    {
        $objectIdResolver->getIdsFromCodes('family', ['shoes', 'ties'])
            ->shouldBeCalled()
            ->willReturn([12, 13]);

        $qb->field('family')
            ->shouldBeCalled()
            ->willReturn($qb);
        $qb->in([12, 13])->shouldBeCalled();

        $this->addFieldFilter('family.code', 'IN', ['shoes', 'ties']);
    }

    function it_adds_a_not_in_filter_on_an_id_field_in_the_query($qb)
    {
        $qb->field('family')
            ->shouldBeCalled()
            ->willReturn($qb);
        $qb->notIn([12, 13])->shouldBeCalled();

        $this->addFieldFilter('family.id', 'NOT IN', [12, 13]);
    }

    function it_adds_a_not_in_filter_on_a_code_field_in_the_query($qb, $objectIdResolver)
    {
        $objectIdResolver->getIdsFromCodes('family', ['shoes', 'ties'])
            ->shouldBeCalled()
            ->willReturn([12, 13]);

        $qb->field('family')
            ->shouldBeCalled()
            ->willReturn($qb);
        $qb->notIn([12, 13])->shouldBeCalled();

        $this->addFieldFilter('family.code', 'NOT IN', ['shoes', 'ties']);
    }

    function it_adds_an_empty_filter_on_an_id_field_in_the_query($qb)
    {
        $qb->field('family')
            ->shouldBeCalled()
            ->willReturn($qb);
        $qb->exists(false)->shouldBeCalled();

        $this->addFieldFilter('family.id', 'EMPTY', null);
    }

    function it_adds_an_empty_filter_on_a_code_field_in_the_query($qb)
    {
        $qb->field('family')
            ->shouldBeCalled()
            ->willReturn($qb);
        $qb->exists(false)->shouldBeCalled();

        $this->addFieldFilter('family.code', 'EMPTY', null);
    }

    function it_adds_a_not_empty_filter_on_an_id_field_in_the_query($qb)
    {
        $qb->field('family')
            ->shouldBeCalled()
            ->willReturn($qb);
        $qb->exists(true)->shouldBeCalled();

        $this->addFieldFilter('family.id', 'NOT EMPTY', null);
    }

    function it_adds_a_not_empty_filter_on_a_code_field_in_the_query($qb)
    {
        $qb->field('family')
            ->shouldBeCalled()
            ->willReturn($qb);
        $qb->exists(true)->shouldBeCalled();

        $this->addFieldFilter('family.code', 'NOT EMPTY', null);
    }

    function it_throws_an_exception_if_value_is_not_an_array()
    {
        $this->shouldThrow(InvalidArgumentException::arrayExpected('family', 'filter', 'family', gettype('not an array')))
            ->during('addFieldFilter', ['family', 'IN', 'not an array']);
    }

    function it_throws_an_exception_if_content_of_array_is_not_integer_or_empty()
    {
        $this->shouldThrow(InvalidArgumentException::numericExpected('family', 'filter', 'family', gettype('WRONG')))
            ->during('addFieldFilter', ['family', 'IN', [1, 2, 'WRONG']]);
    }
}

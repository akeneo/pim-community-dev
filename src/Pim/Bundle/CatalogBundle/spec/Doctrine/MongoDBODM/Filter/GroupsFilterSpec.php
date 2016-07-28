<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface;
use Pim\Component\Catalog\Exception\InvalidArgumentException;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class GroupsFilterSpec extends ObjectBehavior
{
    function let(Builder $qb, ObjectIdResolverInterface $objectIdResolver)
    {
        $this->beConstructedWith(
            $objectIdResolver,
            ['groups.id', 'groups.code'],
            ['IN', 'NOT IN', 'EMPTY', 'NOT EMPTY']
        );
        $this->setQueryBuilder($qb);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\FieldFilterInterface');
    }

    function it_adds_an_in_filter_on_an_id_field_in_the_query($qb)
    {
        $qb->field('groupIds')
            ->shouldBeCalled()
            ->willReturn($qb);
        $qb->in([12, 13])->shouldBeCalled();

        $this->addFieldFilter('groups.id', 'IN', [12, 13]);
    }

    function it_adds_an_in_filter_on_a_code_field_in_the_query($qb, $objectIdResolver)
    {
        $objectIdResolver->getIdsFromCodes('group', ['upsell', 'related'])
            ->shouldBeCalled()
            ->willReturn([12, 13]);

        $qb->field('groupIds')
            ->shouldBeCalled()
            ->willReturn($qb);
        $qb->in([12, 13])->shouldBeCalled();

        $this->addFieldFilter('groups.code', 'IN', ['upsell', 'related']);
    }

    function it_adds_a_not_in_filter_on_an_id_field_in_the_query($qb)
    {
        $qb->field('groupIds')
            ->shouldBeCalled()
            ->willReturn($qb);
        $qb->notIn([12, 13])->shouldBeCalled();

        $this->addFieldFilter('groups.id', 'NOT IN', [12, 13]);
    }

    function it_adds_a_not_in_filter_on_a_code_field_in_the_query($qb, $objectIdResolver)
    {
        $objectIdResolver->getIdsFromCodes('group', ['upsell', 'related'])
            ->shouldBeCalled()
            ->willReturn([12, 13]);

        $qb->field('groupIds')
            ->shouldBeCalled()
            ->willReturn($qb);
        $qb->notIn([12, 13])->shouldBeCalled();

        $this->addFieldFilter('groups.code', 'NOT IN', ['upsell', 'related']);
    }

    function it_adds_an_empty_filter_on_an_id_field_in_the_query($qb)
    {
        $qb->field('groupIds')
            ->shouldBeCalled()
            ->willReturn($qb);
        $qb->size(0)->shouldBeCalled();

        $this->addFieldFilter('groups.id', 'EMPTY', null);
    }

    function it_adds_an_empty_filter_on_a_code_field_in_the_query($qb)
    {
        $qb->field('groupIds')
            ->shouldBeCalled()
            ->willReturn($qb);
        $qb->size(0)->shouldBeCalled();

        $this->addFieldFilter('groups.code', 'EMPTY', null);
    }

    function it_adds_a_not_empty_filter_on_an_id_field_in_the_query($qb)
    {
        $qb->where('this.groupIds.length > 0')->shouldBeCalled();

        $this->addFieldFilter('groups.id', 'NOT EMPTY', null);
    }

    function it_adds_a_not_empty_filter_on_a_code_field_in_the_query($qb)
    {
        $qb->where('this.groupIds.length > 0')->shouldBeCalled();

        $this->addFieldFilter('groups.code', 'NOT EMPTY', null);
    }

    function it_throws_an_exception_if_value_is_not_an_array()
    {
        $this->shouldThrow(
            InvalidArgumentException::arrayExpected('groups', 'filter', 'groups', gettype('not an array'))
        )
            ->during('addFieldFilter', ['groups.id', 'IN', 'not an array']);
    }

    function it_throws_an_exception_if_content_of_array_is_not_integer_or_empty()
    {
        $this->shouldThrow(InvalidArgumentException::numericExpected('groups', 'filter', 'groups', gettype('WRONG')))
            ->during('addFieldFilter', ['groups.id', 'IN', [1, 2, 'WRONG']]);
    }

    function it_returns_supported_fields()
    {
        $this->getFields()->shouldReturn(['groups.id', 'groups.code']);
    }
}

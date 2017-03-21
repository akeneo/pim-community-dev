<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class GroupsFilterSpec extends ObjectBehavior
{
    function let(Builder $qb, ObjectIdResolverInterface $objectIdResolver)
    {
        $this->beConstructedWith(
            $objectIdResolver,
            ['groups.id', 'groups'],
            ['IN', 'NOT IN', 'EMPTY', 'NOT EMPTY']
        );
        $this->setQueryBuilder($qb);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\FieldFilterInterface');
    }

    function it_adds_a_filter_on_codes_by_default($qb, $objectIdResolver)
    {
        $qb->field('groupIds')
            ->shouldBeCalled()
            ->willReturn($qb);
        $qb->in([12, 13])->shouldBeCalled();
        $objectIdResolver->getIdsFromCodes('group', ['foo', 'bar'])->willReturn([12, 13]);

        $this->addFieldFilter('groups', 'IN', ['foo', 'bar']);
    }

    function it_adds_a_filter_on_codes($qb, $objectIdResolver)
    {
        $qb->field('groupIds')
            ->shouldBeCalled()
            ->willReturn($qb);
        $qb->in([12, 13])->shouldBeCalled();
        $objectIdResolver->getIdsFromCodes('group', ['foo', 'bar'])->willReturn([12, 13]);

        $this->addFieldFilter('groups', 'IN', ['foo', 'bar']);
    }

    function it_adds_a_filter_on_ids($qb, $objectIdResolver)
    {
        $qb->field('groupIds')
            ->shouldBeCalled()
            ->willReturn($qb);
        $qb->in([12, 13])->shouldBeCalled();
        $objectIdResolver->getIdsFromCodes(Argument::cetera())->shouldNotBeCalled();

        $this->addFieldFilter('groups.id', 'IN', [12, 13]);
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

        $this->addFieldFilter('groups', 'IN', ['upsell', 'related']);
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

        $this->addFieldFilter('groups', 'NOT IN', ['upsell', 'related']);
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

        $this->addFieldFilter('groups', 'EMPTY', null);
    }

    function it_adds_a_not_empty_filter_on_an_id_field_in_the_query($qb)
    {
        $qb->where('this.groupIds.length > 0')->shouldBeCalled();

        $this->addFieldFilter('groups.id', 'NOT EMPTY', null);
    }

    function it_adds_a_not_empty_filter_on_a_code_field_in_the_query($qb)
    {
        $qb->where('this.groupIds.length > 0')->shouldBeCalled();

        $this->addFieldFilter('groups', 'NOT EMPTY', null);
    }

    function it_throws_an_exception_if_value_is_not_an_array()
    {
        $this->shouldThrow(InvalidPropertyTypeException::arrayExpected('groups', 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\GroupsFilter', 'not an array'))
            ->during('addFieldFilter', ['groups.id', 'IN', 'not an array']);
    }

    function it_throws_an_exception_if_content_of_array_is_not_string_or_numeric_or_empty()
    {
        $this->shouldThrow(InvalidPropertyTypeException::numericExpected('groups', 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\GroupsFilter', 'WRONG'))
            ->during('addFieldFilter', ['groups.id', 'IN', [1, 2, 'WRONG']]);

        $this->shouldThrow(InvalidPropertyTypeException::stringExpected('groups', 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\GroupsFilter', false))
            ->during('addFieldFilter', ['groups', 'IN', ['a_code', false]]);
    }

    function it_returns_supported_fields()
    {
        $this->getFields()->shouldReturn(['groups.id', 'groups']);
    }
}

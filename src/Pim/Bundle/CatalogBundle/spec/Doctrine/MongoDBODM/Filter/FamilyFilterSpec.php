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
class FamilyFilterSpec extends ObjectBehavior
{
    function let(Builder $qb, ObjectIdResolverInterface $objectIdResolver)
    {
        $this->beConstructedWith(
            $objectIdResolver,
            ['family.id', 'family'],
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

    function it_adds_a_filter_on_codes_by_default($qb, $objectIdResolver)
    {
        $qb->field('family')->shouldBeCalled()->willReturn($qb);
        $qb->in([12, 56])->shouldBeCalled();
        $objectIdResolver->getIdsFromCodes('family', ['foo', 'bar'])->willReturn([12, 56]);

        $this->addFieldFilter('family', 'IN', ['foo', 'bar']);
    }

    function it_adds_a_filter_on_codes($qb, $objectIdResolver)
    {
        $qb->field('family')->shouldBeCalled()->willReturn($qb);
        $qb->in([12, 56])->shouldBeCalled();
        $objectIdResolver->getIdsFromCodes('family', ['foo', 'bar'])->willReturn([12, 56]);

        $this->addFieldFilter('family.code', 'IN', ['foo', 'bar']);
    }

    function it_adds_a_filter_on_ids($qb, $objectIdResolver)
    {
        $qb->field('family')->shouldBeCalled()->willReturn($qb);
        $qb->in([12, 56])->shouldBeCalled();
        $objectIdResolver->getIdsFromCodes(Argument::cetera())->shouldNotBeCalled();

        $this->addFieldFilter('family.id', 'IN', [12, 56]);
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

        $this->addFieldFilter('family', 'IN', ['shoes', 'ties']);
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

        $this->addFieldFilter('family', 'NOT IN', ['shoes', 'ties']);
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

        $this->addFieldFilter('family', 'EMPTY', null);
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

        $this->addFieldFilter('family', 'NOT EMPTY', null);
    }

    function it_throws_an_exception_if_value_is_not_an_array()
    {
        $this->shouldThrow(InvalidPropertyTypeException::arrayExpected(
            'family',
            'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\FamilyFilter',
            'not an array'
        ))->during('addFieldFilter', ['family', 'IN', 'not an array']);
    }

    function it_throws_an_exception_if_content_of_array_is_not_string_or_numeric_or_empty()
    {
        $this->shouldThrow(InvalidPropertyTypeException::stringExpected(
            'family',
            'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\FamilyFilter',
            false
        ))
            ->during('addFieldFilter', ['family', 'IN', ['a_code', false]]);
    }

    function it_returns_supported_fields()
    {
        $this->getFields()->shouldReturn(['family.id', 'family']);
    }
}

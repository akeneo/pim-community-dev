<?php

namespace spec\Pim\Bundle\EnrichBundle\Elasticsearch\Sorter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Bundle\CatalogBundle\Elasticsearch\Sorter\Field\BaseFieldSorter;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\EnrichBundle\Elasticsearch\Sorter\InGroupSorter;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Exception\InvalidDirectionException;
use Pim\Component\Catalog\Query\Sorter\Directions;
use Pim\Component\Catalog\Query\Sorter\FieldSorterInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;

class InGroupSorterSpec extends ObjectBehavior
{
    function let(
        GroupRepositoryInterface $groupRepository
    ) {
        $this->beConstructedWith($groupRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(BaseFieldSorter::class);
    }

    function it_is_a_fieldSorter()
    {
        $this->shouldImplement(FieldSorterInterface::class);
    }

    function it_supports_fields()
    {
        $this->supportsField('in_group_4')->shouldReturn(true);
        $this->supportsField('a_not_supported_field')->shouldReturn(false);
    }

    function it_add_ascending_sorter_with_field(
        $groupRepository,
        SearchQueryBuilder $sqb,
        Group $group
    ) {
        $this->setQueryBuilder($sqb);

        $groupRepository->find(1)->willReturn($group);
        $group->getId()->willReturn(1);
        $group->getCode()->willReturn('group_code');

        $sqb->addSort(
            [
                'in_group.group_code' => [
                    'order'   => 'ASC',
                    'missing' => '_first',
                    'unmapped_type'=> 'boolean',
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldSorter('in_group_1', Directions::ASCENDING);
    }

    function it_add_descending_sorter_with_field(
        $groupRepository,
        SearchQueryBuilder $sqb,
        Group $group
    ) {
        $this->setQueryBuilder($sqb);

        $groupRepository->find(1)->willReturn($group);
        $group->getId()->willReturn(1);
        $group->getCode()->willReturn('group_code');

        $sqb->addSort(
            [
                'in_group.group_code' => [
                    'order'   => 'DESC',
                    'missing' => '_last',
                    'unmapped_type'=> 'boolean',
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldSorter('in_group_1', Directions::DESCENDING);
    }

    function it_throws_an_exception_when_group_is_null()
    {
        $this->shouldThrow(
            new InvalidArgumentException(
                InGroupSorter::class,
                'Unsupported field "in_group_bad_identifier" for InGroupSorter.'
            )
        )->during('addFieldSorter', ['in_group_bad_identifier', Directions::ASCENDING]);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(
        $groupRepository,
        Group $group
    ) {
        $groupRepository->find(1)->willReturn($group);
        $group->getId()->willReturn(1);
        $group->getCode()->willReturn('group_code');

        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the sorter.')
        )->during('addFieldSorter', ['in_group_1', Directions::ASCENDING]);
    }

    function it_throws_an_exception_when_the_directions_does_not_exist(
        $groupRepository,
        SearchQueryBuilder $sqb,
        Group $group
    ) {
        $this->setQueryBuilder($sqb);

        $groupRepository->find(1)->willReturn($group);
        $group->getId()->willReturn(1);
        $group->getCode()->willReturn('group_code');

        $this->shouldThrow(
            InvalidDirectionException::notSupported(
                'A_BAD_DIRECTION',
                InGroupSorter::class
            )
        )->during('addFieldSorter', ['in_group_1', 'A_BAD_DIRECTION']);
    }
}

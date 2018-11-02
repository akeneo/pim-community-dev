<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Field;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Field\BaseFieldSorter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\FieldSorterInterface;

class BaseFieldSorterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['updated', 'created']);
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
        $this->supportsField('updated')->shouldReturn(true);
        $this->supportsField('a_not_supported_field')->shouldReturn(false);
    }

    function it_add_ascending_sorter_with_field(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $sqb->addSort(
            [
                'updated' => [
                    "order" => 'ASC',
                    "missing" => "_last"
                ]
            ]
        )->shouldBeCalled();

        $this->addFieldSorter('updated', Directions::ASCENDING);
    }

    function it_add_descending_sorter_with_field(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $sqb->addSort(
            [
                'updated' => [
                    "order" => 'DESC',
                    "missing" => "_last"
                ]
            ]
        )->shouldBeCalled();

        $this->addFieldSorter('updated', Directions::DESCENDING);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized()
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the sorter.')
        )->during('addFieldSorter', ['updated', Directions::ASCENDING]);
    }

    function it_throws_an_exception_when_the_directions_does_not_exist(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidDirectionException::notSupported(
                'A_BAD_DIRECTION',
                BaseFieldSorter::class
            )
        )->during('addFieldSorter', ['updated', 'A_BAD_DIRECTION']);
    }
}

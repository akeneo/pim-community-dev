<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\IdentifierSorter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\AttributeSorterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\FieldSorterInterface;

class IdentifierSorterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['identifier'], ['pim_catalog_identifier']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IdentifierSorter::class);
    }

    function it_is_a_field_sorter()
    {
        $this->shouldImplement(FieldSorterInterface::class);
    }

    function it_is_an_attribute_sorter()
    {
        $this->shouldImplement(AttributeSorterInterface::class);
    }

    function it_supports_fields()
    {
        $this->supportsField('identifier')->shouldReturn(true);
        $this->supportsField('a_not_supported_field')->shouldReturn(false);
    }

    function it_supports_attributes(AttributeInterface $sku, AttributeInterface $metric)
    {
        $sku->getType()->willReturn('pim_catalog_identifier');
        $metric->getType()->willReturn('pim_catalog_metric');

        $this->supportsAttribute($sku)->shouldReturn(true);
        $this->supportsAttribute($metric)->shouldReturn(false);
    }

    function it_add_ascending_sorter_with_field(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $sqb->addSort(
            [
                'identifier' => [
                    "order"   => 'ASC',
                    "missing" => "_last",
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldSorter('identifier', Directions::ASCENDING, 'en_US', 'mobile');
    }

    function it_add_descending_sorter_with_field(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $sqb->addSort(
            [
                'identifier' => [
                    "order"   => 'DESC',
                    "missing" => "_last",
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldSorter('identifier', Directions::DESCENDING);
    }

    function it_add_ascending_sorter_with_attribute(
        AttributeInterface $sku,
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $sqb->addSort(
            [
                'identifier' => [
                    "order"   => 'ASC',
                    "missing" => "_last",
                ],
            ]
        )->shouldBeCalled();

        $this->addAttributeSorter($sku, Directions::ASCENDING);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized()
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the sorter.')
        )->during('addFieldSorter', ['identifier', Directions::ASCENDING]);
    }

    function it_throws_an_exception_when_the_directions_does_not_exist(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidDirectionException::notSupported(
                'A_BAD_DIRECTION',
                IdentifierSorter::class
            )
        )->during('addFieldSorter', ['identifier', 'A_BAD_DIRECTION', 'en_US', 'mobile']);
    }
}

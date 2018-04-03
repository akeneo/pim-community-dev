<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch\Sorter\Field;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Bundle\CatalogBundle\Elasticsearch\Sorter\Field\CompletenessSorter;
use Pim\Component\Catalog\Exception\InvalidDirectionException;
use Pim\Component\Catalog\Query\Sorter\Directions;
use Pim\Component\Catalog\Query\Sorter\FieldSorterInterface;

class CompletenessSorterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['completeness']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CompletenessSorter::class);
    }

    function it_is_a_fieldSorter()
    {
        $this->shouldImplement(FieldSorterInterface::class);
    }

    function it_supports_fields()
    {
        $this->supportsField('completeness')->shouldReturn(true);
        $this->supportsField('a_not_supported_field')->shouldReturn(false);
    }

    function it_add_ascending_sorter_with_field(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $sqb->addSort(
            [
                'completeness.mobile.en_US' => [
                    "order"   => 'ASC',
                    "missing" => "_last"
                ]
            ]
        )->shouldBeCalled();

        $this->addFieldSorter('completeness', Directions::ASCENDING, 'en_US', 'mobile');
    }

    function it_add_descending_sorter_with_field(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $sqb->addSort(
            [
                'completeness.mobile.en_US' => [
                    "order"   => 'DESC',
                    "missing" => "_last"
                ]
            ]
        )->shouldBeCalled();

        $this->addFieldSorter('completeness', Directions::DESCENDING, 'en_US', 'mobile');
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized()
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the sorter.')
        )->during('addFieldSorter', ['completeness', Directions::ASCENDING, 'en_US', 'mobile']);
    }

    function it_throws_an_exception_when_the_directions_does_not_exist(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidDirectionException::notSupported(
                'A_BAD_DIRECTION',
                CompletenessSorter::class
            )
        )->during('addFieldSorter', ['completeness', 'A_BAD_DIRECTION', 'en_US', 'mobile']);
    }

    function it_throws_an_exception_when_locale_is_null(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::valueNotEmptyExpected(
                'locale',
                CompletenessSorter::class
            )
        )->during('addFieldSorter', ['completeness', 'A_BAD_DIRECTION', null, 'mobile']);
    }

    function it_throws_an_exception_when_scope_is_null(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyException::valueNotEmptyExpected(
                'scope',
                CompletenessSorter::class
            )
        )->during('addFieldSorter', ['completeness', 'A_BAD_DIRECTION', 'en_US', null]);
    }
}

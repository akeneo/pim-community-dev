<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Field;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Field\FamilySorter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\FieldSorterInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;

class FamilySorterSpec extends ObjectBehavior
{
    function let(LocaleRepositoryInterface $localeRepository)
    {
        $this->beConstructedWith($localeRepository, ['family']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilySorter::class);
    }

    function it_is_a_fieldSorter()
    {
        $this->shouldImplement(FieldSorterInterface::class);
    }

    function it_supports_fields()
    {
        $this->supportsField('family')->shouldReturn(true);
        $this->supportsField('a_not_supported_field')->shouldReturn(false);
    }

    function it_add_ascending_sorter_with_field(SearchQueryBuilder $sqb)
    {
        $sqb->addSort(
            [
                'family.code'         => [
                    'order'   => 'ASC',
                    'missing' => '_last',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldSorter('family', Directions::ASCENDING);
    }

    function it_add_ascending_sorter_with_field_and_locale($localeRepository, SearchQueryBuilder $sqb)
    {
        $localeRepository->getActivatedLocaleCodes()->willReturn(['en_US', 'fr_FR', 'de_DE']);

        $sqb->addSort(
            [
                'family.labels.en_US' => [
                    'order'         => 'ASC',
                    'unmapped_type' => 'string',
                    'missing'       => '_last',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addSort(
            [
                'family.code'         => [
                    'order'   => 'ASC',
                    'missing' => '_last',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldSorter('family', Directions::ASCENDING, 'en_US');
    }

    function it_add_descending_sorter_with_field(SearchQueryBuilder $sqb)
    {
        $sqb->addSort(
            [
                'family.code'         => [
                    'order'   => 'DESC',
                    'missing' => '_last',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldSorter('family', Directions::DESCENDING);
    }

    function it_add_descending_sorter_with_field_and_locale($localeRepository, SearchQueryBuilder $sqb)
    {
        $localeRepository->getActivatedLocaleCodes()->willReturn(['en_US', 'fr_FR', 'de_DE']);

        $sqb->addSort(
            [
                'family.labels.en_US' => [
                    'order'         => 'DESC',
                    'unmapped_type' => 'string',
                    'missing'       => '_last',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addSort(
            [
                'family.code'         => [
                    'order'   => 'DESC',
                    'missing' => '_last',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldSorter('family', Directions::DESCENDING, 'en_US');
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized()
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the sorter.')
        )->during('addFieldSorter', ['family', Directions::ASCENDING, 'en_US']);
    }

    function it_throws_an_exception_when_the_directions_does_not_exist(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidDirectionException::notSupported(
                'A_BAD_DIRECTION',
                FamilySorter::class
            )
        )->during('addFieldSorter', ['family', 'A_BAD_DIRECTION']);
    }

    function it_throws_an_exception_when_locale_is_not_activated($localeRepository, SearchQueryBuilder $sqb)
    {
        $localeRepository->getActivatedLocaleCodes()->willReturn(['de_DE']);

        $this->setQueryBuilder($sqb);
        $this->shouldThrow(
            new \InvalidArgumentException('Expects a valid locale code to filter on family labels. "fr_FR" given.')
        )->during('addFieldSorter', ['family', Directions::DESCENDING, 'fr_FR']);
    }
}

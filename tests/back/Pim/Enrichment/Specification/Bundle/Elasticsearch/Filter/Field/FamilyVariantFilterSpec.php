<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\FamilyVariantFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface;

class FamilyVariantFilterSpec extends ObjectBehavior
{
    function let(FamilyVariantRepositoryInterface $familyVariantRepository)
    {
        $this->beConstructedWith(
            $familyVariantRepository,
            ['family_variant'],
            ['IN', 'NOT IN', 'EMPTY', 'NOT EMPTY']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariantFilter::class);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn([
            'IN',
            'NOT IN',
            'EMPTY',
            'NOT EMPTY',
        ]);
        $this->supportsOperator('EMPTY')->shouldReturn(true);
        $this->supportsOperator('DOES NOT CONTAIN')->shouldReturn(false);
    }

    function it_supports_family_variant_field()
    {
        $this->supportsField('family_variant')->shouldReturn(true);
        $this->supportsField('not_supported_field')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_in_list(
        $familyVariantRepository,
        SearchQueryBuilder $sqb,
        FamilyVariantInterface $familyVariant
    ) {
        $familyVariantRepository->findOneByIdentifier('familyVar')->willReturn($familyVariant);

        $sqb->addFilter(
            [
                'terms' => [
                    'family_variant' => ['familyVar'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('family_variant', Operators::IN_LIST, ['familyVar'], null, null, []);
    }

    function it_adds_a_filter_with_operator_not_in_list(
        $familyVariantRepository,
        SearchQueryBuilder $sqb,
        FamilyVariantInterface $familyVariant
    ) {
        $familyVariantRepository->findOneByIdentifier('familyVar')->willReturn($familyVariant);

        $sqb->addMustNot(
            [
                'terms' => [
                    'family_variant' => ['familyVar'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('family_variant', Operators::NOT_IN_LIST, ['familyVar'], null, null, []);
    }

    function it_adds_a_filter_with_operator_is_empty(
        $familyVariantRepository,
        SearchQueryBuilder $sqb,
        FamilyVariantInterface $familyVariant
    ) {
        $familyVariantRepository->findOneByIdentifier('familyVar')->willReturn($familyVariant);
        $sqb->addMustNot(
            [
                'exists' => ['field' => 'family_variant'],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('family_variant', Operators::IS_EMPTY, ['familyVar'], null, null, []);
    }

    function it_adds_a_filter_with_operator_is_not_empty(
        $familyVariantRepository,
        SearchQueryBuilder $sqb,
        FamilyVariantInterface $familyVariant
    ) {
        $familyVariantRepository->findOneByIdentifier('familyVar')->willReturn($familyVariant);
        $sqb->addFilter(
            [
                'exists' => ['field' => 'family_variant'],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('family_variant', Operators::IS_NOT_EMPTY, ['familyVar'], null, null, []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized()
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addFieldFilter', ['family_variant', Operators::IN_LIST, ['familyVar'], null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array_with_in_list(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'family_variant',
                FamilyVariantFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addFieldFilter', ['family_variant', Operators::IN_LIST, 'NOT_AN_ARRAY', null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array_with_not_in_list(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'family_variant',
                FamilyVariantFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addFieldFilter', ['family_variant', Operators::NOT_IN_LIST, 'NOT_AN_ARRAY', null, null, []]);
    }

    function it_does_not_throws_an_exception_when_the_given_value_is_not_an_array_with_is_empty(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldNotThrow(
            InvalidPropertyTypeException::arrayExpected(
                'family_variant',
                FamilyVariantFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addFieldFilter', ['family_variant', Operators::IS_EMPTY, 'NOT_AN_ARRAY', null, null, []]);
    }

    function it_does_not_throws_an_exception_when_the_given_value_is_not_an_array_with_is_not_empty(
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $this->shouldNotThrow(
            InvalidPropertyTypeException::arrayExpected(
                'family_variant',
                FamilyVariantFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addFieldFilter', ['family_variant', Operators::IS_NOT_EMPTY, 'NOT_AN_ARRAY', null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_identifier(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'family_variant',
                FamilyVariantFilter::class,
                false
            )
        )->during('addFieldFilter', ['family_variant', Operators::IN_LIST, [false], null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_a_known_family_variant(
        $familyVariantRepository,
        SearchQueryBuilder $sqb
    ) {
        $familyVariantRepository->findOneByIdentifier('UNKNOWN_FAMILY_VARIANT')->willReturn(null);
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            new ObjectNotFoundException('Object "family_variant" with code "UNKNOWN_FAMILY_VARIANT" does not exist')
        )->during('addFieldFilter', ['family_variant', Operators::IN_LIST, ['UNKNOWN_FAMILY_VARIANT'], null, null, []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        $familyVariantRepository,
        SearchQueryBuilder $sqb,
        FamilyVariantInterface $familyVariant
    ) {
        $familyVariantRepository->findOneByIdentifier('familyVar')->willReturn($familyVariant);
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                FamilyVariantFilter::class
            )
        )->during('addFieldFilter', ['family_variant', Operators::IN_CHILDREN_LIST, ['familyVar'], null, null, []]);
    }
}

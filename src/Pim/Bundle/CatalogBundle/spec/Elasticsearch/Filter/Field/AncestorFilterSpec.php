<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field\AncestorFilter;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;

class AncestorFilterSpec extends ObjectBehavior
{
    public function let() {
        $this->beConstructedWith(['ancestor.id'], [Operators::IN_LIST, Operators::NOT_IN_LIST]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AncestorFilter::class);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
    }

    function it_supports_operators()
    {
        $this->supportsOperator(Operators::IN_LIST)->shouldReturn(true);
        $this->supportsOperator(Operators::NOT_IN_LIST)->shouldReturn(true);
        $this->supportsOperator(Operators::EQUALS)->shouldReturn(false);
    }

    function it_supports_ancestor_id_field()
    {
        $this->supportsField('ancestor.id')->shouldReturn(true);
        $this->supportsField('ancestor.code')->shouldReturn(false);
        $this->supportsField('wrong_field')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_in_list(
        SearchQueryBuilder $sqb,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $sqb->addShould(
            [
                [
                    'terms' => ['ancestors.ids' => ['product_model_1', 'product_model_2']],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter(
            'ancestor.id',
            Operators::IN_LIST,
            ['product_model_1', 'product_model_2'],
            null,
            null,
            []
        );
    }

    function it_adds_a_filter_with_operator_not_in_list(SearchQueryBuilder $sqb) {
        $sqb->addMustNot(
            [
                'terms' => ['ancestors.ids' => ['product_model_1', 'product_model_2']],
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'ancestors.ids',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter(
            'ancestor.id',
            Operators::NOT_IN_LIST,
            ['product_model_1', 'product_model_2'],
            null,
            null,
            []
        );
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized()
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addFieldFilter', ['ancestors.ids', Operators::EQUALS, 'product_model_id', null, null, []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                AncestorFilter::class
            )
        )->during('addFieldFilter', ['ancestor.id', Operators::IN_CHILDREN_LIST, null, null, null, []]);
    }

    function it_throws_an_exception_if_the_value_is_not_an_array(
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected('ancestors', AncestorFilter::class, 123)
        )->during('addFieldFilter', ['parent', Operators::IN_LIST, 123, null, null, []]);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected('ancestors', AncestorFilter::class, 'wrong_value')
        )->during('addFieldFilter', ['parent', Operators::IN_LIST, 'wrong_value', null, null, []]);
    }
}

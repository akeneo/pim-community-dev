<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\AbstractFieldFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\SelfAndAncestorFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;

class SelfAndAncestorFilterSpec extends ObjectBehavior
{
    function let(
        ProductModelRepositoryInterface $productModelRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->beConstructedWith(
            $productModelRepository,
            $productRepository,
            ['self_and_ancestors.id'],
            ['IN', 'NOT IN']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SelfAndAncestorFilter::class);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
        $this->shouldBeAnInstanceOf(AbstractFieldFilter::class);
    }

    function it_supports_operator()
    {
        $this->getOperators()->shouldReturn(['IN', 'NOT IN']);
        $this->supportsOperator('IN')->shouldReturn(true);
        $this->supportsOperator('NOT IN')->shouldReturn(true);
        $this->supportsOperator('=')->shouldReturn(false);
    }

    function it_supports_field()
    {
        $this->supportsField('self_and_ancestors.id')->shouldReturn(true);
        $this->supportsField('not_supported_field')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_IN(
        $productModelRepository,
        $productRepository,
        SearchQueryBuilder $sqb,
        ProductModelInterface $productModel,
        ProductInterface $product
    ) {
        $productModelRepository->findOneBy(['id' => '1'])->shouldNotBeCalled();
        $productModelRepository->findOneBy(['id' => '2'])->willReturn($productModel);
        $productRepository->findOneBy(['id' => '1'])->willReturn($product);
        $productRepository->findOneBy(['id' => '2'])->shouldNotBeCalled();

        $sqb->addShould(
            [
                'terms' => ['id' => ['product_1', 'product_model_2']],
            ]
        )->shouldBeCalled();

        $sqb->addShould(
            [
                'terms' => ['ancestors.ids' => ['product_1', 'product_model_2']],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter(
            'self_and_ancestors.id',
            Operators::IN_LIST,
            ['product_1', 'product_model_2'],
            null,
            null,
            []
        );
    }

    function it_adds_a_filter_with_operator_NOT_IN(
        $productModelRepository,
        $productRepository,
        SearchQueryBuilder $sqb,
        ProductModelInterface $productModel,
        ProductInterface $product
    ) {
        $productModelRepository->findOneBy(['id' => '1'])->shouldNotBeCalled();
        $productModelRepository->findOneBy(['id' => '2'])->willReturn($productModel);
        $productRepository->findOneBy(['id' => '1'])->willReturn($product);
        $productRepository->findOneBy(['id' => '2'])->shouldNotBeCalled();

        $sqb->addMustNot(
            [
                'terms' => ['id' => ['product_1', 'product_model_2']],
            ]
        )->shouldBeCalled();

        $sqb->addMustNot(
            [
                'terms' => ['ancestors.ids' => ['product_1', 'product_model_2']],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter(
            'self_and_ancestors.id',
            Operators::NOT_IN_LIST,
            ['product_1', 'product_model_2'],
            null,
            null,
            []
        );
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized()
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addFieldFilter', ['self_and_ancestors.id', Operators::IN_LIST, ['product_1'], null, null, []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);
        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                SelfAndAncestorFilter::class
            )
        )->during('addFieldFilter', ['self_and_ancestors.id', Operators::IN_CHILDREN_LIST, ['product_1'], null, null, []]);
    }

    function it_throws_if_the_value_is_not_a_product_id_nor_a_product_model_id(
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);
        $this->shouldThrow(
            new ObjectNotFoundException(
                'Object with ID "not_a_product_id_nor_a_product_model_id" does not exist as a product nor as a product model'
            )
        )->during(
            'addFieldFilter',
            ['self_and_ancestors.id', Operators::IN_LIST, ['not_a_product_id_nor_a_product_model_id'], null, null, []]
        );
    }
}

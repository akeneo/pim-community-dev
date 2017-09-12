<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field\AbstractFieldFilter;
use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field\FamilyFilter;
use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field\ParentFilter;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;

class ParentFilterSpec extends ObjectBehavior
{
    function let(ProductModelRepositoryInterface $productModelRepository)
    {
        $this->beConstructedWith($productModelRepository, ['parent'], ['EMPTY']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ParentFilter::class);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
        $this->shouldBeAnInstanceOf(AbstractFieldFilter::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['EMPTY']);
        $this->supportsOperator('EMPTY')->shouldReturn(true);
        $this->supportsOperator('DOES NOT CONTAIN')->shouldReturn(false);
    }

    function it_supports_parent_field()
    {
        $this->supportsField('parent')->shouldReturn(true);
        $this->supportsField('a_not_supported_field')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_is_empty(
        SearchQueryBuilder $sqb
    ) {
        $sqb->addMustNot(
            [
                'exists' => ['field' => 'parent'],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('parent', Operators::IS_EMPTY, null, null, null, []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized()
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addFieldFilter', ['family', Operators::IN_LIST, ['familyA'], null, null, []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                ParentFilter::class
            )
        )->during('addFieldFilter', ['parent', Operators::IN_CHILDREN_LIST, null, null, null, []]);
    }

    function it_throws_an_exception_if_the_parent_doesn_t_exist(
        $productModelRepository,
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $productModelRepository->findOneByIdentifier('jambon')->willReturn(null);

        $this->shouldThrow(ObjectNotFoundException::class)
            ->during('addFieldFilter', ['parent', Operators::IN_LIST, ['jambon'], null, null, []]);
    }
}

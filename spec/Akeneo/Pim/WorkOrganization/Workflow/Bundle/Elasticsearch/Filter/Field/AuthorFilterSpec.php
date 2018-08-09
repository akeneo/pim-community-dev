<?php

namespace spec\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Field\AuthorFilter;

class AuthorFilterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['=', '!=']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AuthorFilter::class);
    }

    function it_is_an_attribute_filter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized() {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addFieldFilter', ['author', Operators::EQUALS, [1, 2], 'en_US', 'ecommerce', []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'author',
                AuthorFilter::class,
                123
            )
        )->during('addFieldFilter', ['author', Operators::EQUALS, 123, 'en_US', 'ecommerce', []]);
    }

    function it_adds_an_author_filter(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $clause = [
            'terms' => [
                'author' => [1, 2],
            ],
        ];

        $sqb->addFilter($clause)->shouldBeCalled();
        $this->addFieldFilter('author', Operators::EQUALS, [1, 2], 'en_US', 'ecommerce', []);
    }
}

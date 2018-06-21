<?php

namespace spec\Akeneo\Pim\Permission\Bundle\Enrichment\Storage\ElasticsearchAndSql\CategoryTree;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\ElasticsearchAndSql\CategoryTree\ListGrantedChildrenCategoriesWithCountNotIncludingSubCategories;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;

class ListGrantedChildrenCategoriesWithCountNotIncludingSubCategoriesSpec extends ObjectBehavior
{
    function let(Connection $connection, Client $client)
    {
        $this->beConstructedWith($connection, $client, 'index');
    }

    function it_lists_children_categories_with_count()
    {
        $this->shouldImplement(Query\ListChildrenCategoriesWithCountNotIncludingSubCategories::class);
    }

    function it_lists_children_categories_with_count_not_including_sub_categories()
    {
        $this->shouldHaveType(ListGrantedChildrenCategoriesWithCountNotIncludingSubCategories::class);
    }
}

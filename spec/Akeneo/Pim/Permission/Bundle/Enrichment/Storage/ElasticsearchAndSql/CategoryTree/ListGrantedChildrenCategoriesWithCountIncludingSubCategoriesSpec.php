<?php

namespace spec\Akeneo\Pim\Permission\Bundle\Enrichment\Storage\ElasticsearchAndSql\CategoryTree;

use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\ElasticsearchAndSql\CategoryTree\ListGrantedChildrenCategoriesWithCountIncludingSubCategories;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;

class ListGrantedChildrenCategoriesWithCountIncludingSubCategoriesSpec extends ObjectBehavior
{
    function let(Connection $connection, Client $client)
    {
        $this->beConstructedWith($connection, $client, 'index');
    }

    function it_lists_children_categories_with_count()
    {
        $this->shouldImplement(Query\ListChildrenCategoriesWithCountIncludingSubCategories::class);
    }

    function it_lists_children_categories_with_count_including_sub_categories()
    {
        $this->shouldHaveType(ListGrantedChildrenCategoriesWithCountIncludingSubCategories::class);
    }
}

<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Enrichment\Storage\ElasticsearchAndSql\CategoryTree;

use Akeneo\Category\Domain\Component\CategoryTree\Query\ListChildrenCategoriesWithCountIncludingSubCategories;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\ElasticsearchAndSql\CategoryTree\ListGrantedChildrenCategoriesWithCountIncludingSubCategories;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
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
        $this->shouldImplement(ListChildrenCategoriesWithCountIncludingSubCategories::class);
    }

    function it_lists_children_categories_with_count_including_sub_categories()
    {
        $this->shouldHaveType(ListGrantedChildrenCategoriesWithCountIncludingSubCategories::class);
    }
}

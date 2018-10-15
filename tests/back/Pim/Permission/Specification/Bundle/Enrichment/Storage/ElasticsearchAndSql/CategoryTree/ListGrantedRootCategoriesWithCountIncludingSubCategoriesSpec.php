<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Enrichment\Storage\ElasticsearchAndSql\CategoryTree;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query\ListRootCategoriesWithCountIncludingSubCategories;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\ElasticsearchAndSql\CategoryTree\ListGrantedRootCategoriesWithCountIncludingSubCategories;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;

class ListGrantedRootCategoriesWithCountIncludingSubCategoriesSpec extends ObjectBehavior
{
    function let(Connection $connection, Client $client)
    {
        $this->beConstructedWith($connection, $client, 'index');
    }

    function it_lists_root_categories_with_count()
    {
        $this->shouldImplement(ListRootCategoriesWithCountIncludingSubCategories::class);
    }

    function it_lists_root_categories_with_count_including_sub_categories()
    {
        $this->shouldHaveType(ListGrantedRootCategoriesWithCountIncludingSubCategories::class);
    }
}

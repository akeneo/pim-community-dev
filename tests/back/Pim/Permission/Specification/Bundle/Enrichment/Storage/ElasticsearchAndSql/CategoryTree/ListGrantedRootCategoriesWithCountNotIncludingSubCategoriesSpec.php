<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Enrichment\Storage\ElasticsearchAndSql\CategoryTree;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query\ListRootCategoriesWithCountNotIncludingSubCategories;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\ElasticsearchAndSql\CategoryTree\ListGrantedRootCategoriesWithCountNotIncludingSubCategories;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;

class ListGrantedRootCategoriesWithCountNotIncludingSubCategoriesSpec extends ObjectBehavior
{
    function let(Connection $connection, Client $client)
    {
        $this->beConstructedWith($connection, $client, 'index');
    }

    function it_lists_root_categories_with_count()
    {
        $this->shouldImplement(ListRootCategoriesWithCountNotIncludingSubCategories::class);
    }

    function it_lists_root_categories_with_count_not_including_sub_categories()
    {
        $this->shouldHaveType(ListGrantedRootCategoriesWithCountNotIncludingSubCategories::class);
    }
}

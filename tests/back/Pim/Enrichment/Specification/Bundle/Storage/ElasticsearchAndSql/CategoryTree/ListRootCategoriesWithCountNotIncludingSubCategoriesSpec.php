<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\CategoryTree;

use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\CategoryTree\ListRootCategoriesWithCountNotIncludingSubCategories;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;

class ListRootCategoriesWithCountNotIncludingSubCategoriesSpec extends ObjectBehavior
{
    function let(Connection $connection, Client $client)
    {
        $this->beConstructedWith($connection, $client);
    }

    function it_lists_children_categories_with_count()
    {
        $this->shouldImplement(Query\ListRootCategoriesWithCountNotIncludingSubCategories::class);
    }

    function it_lists_children_categories_with_count_not_including_sub_categories()
    {
        $this->shouldHaveType(ListRootCategoriesWithCountNotIncludingSubCategories::class);
    }
}

<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\CategoryTree;

use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\CategoryTree\ListRootCategoriesWithCountIncludingSubCategories;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;

class ListRootCategoriesWithCountIncludingSubCategoriesSpec extends ObjectBehavior
{
    function let(Connection $connection, Client $client)
    {
        $this->beConstructedWith($connection, $client);
    }

    function it_lists_children_categories_with_count()
    {
        $this->shouldImplement(Query\ListRootCategoriesWithCountIncludingSubCategories::class);
    }

    function it_lists_children_categories_with_count_including_sub_categories()
    {
        $this->shouldHaveType(ListRootCategoriesWithCountIncludingSubCategories::class);
    }
}

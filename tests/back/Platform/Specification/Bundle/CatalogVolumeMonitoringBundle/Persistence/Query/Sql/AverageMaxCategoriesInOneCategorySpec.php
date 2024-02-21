<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Result;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql\AverageMaxCategoriesInOneCategory;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Prophecy\Argument;

class AverageMaxCategoriesInOneCategorySpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection, -1);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AverageMaxCategoriesInOneCategory::class);
    }

    function it_is_an_average_and_max_query()
    {
        $this->shouldImplement(AverageMaxQuery::class);
    }

    function it_gets_average_and_max_volume(Connection $connection, Result $statement)
    {
        $connection->executeQuery(Argument::type('string'))->willReturn($statement);
        $statement->fetchAssociative()->willReturn(['average' => '4', 'max' => '10']);
        $this->fetch()->shouldBeLike(new AverageMaxVolumes(10, 4, 'average_max_categories_in_one_category'));
    }
}

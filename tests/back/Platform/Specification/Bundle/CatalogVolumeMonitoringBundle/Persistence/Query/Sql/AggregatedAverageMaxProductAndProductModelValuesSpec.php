<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql\AggregatedAverageMaxProductAndProductModelValues;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AggregatedAverageMaxProductAndProductModelValuesSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection, 10);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AggregatedAverageMaxProductAndProductModelValues::class);
    }

    function it_is_an_average_max_query()
    {
        $this->shouldImplement(AverageMaxQuery::class);
    }

    function it_fetches_an_average_max_volume(Connection $connection, Result $statement)
    {
        $connection->executeQuery(Argument::type('string'))->willReturn($statement);
        $statement->fetchAssociative()->willReturn(
            [
                'max' => 12,
                'average' => 7,
            ]
        );

        $this->fetch()->shouldBeLike(new AverageMaxVolumes(12, 7, 'average_max_product_and_product_model_values'));
    }

    function it_fetches_an_average_max_with_empty_values_if_no_aggregated_volume_has_been_found(
        Connection $connection,
        Result $statement
    ) {
        $connection->executeQuery(Argument::type('string'))->willReturn($statement);
        $statement->fetchAssociative()->willReturn(null);

        $this->fetch()->shouldBeLike(new AverageMaxVolumes(0, 0, 'average_max_product_and_product_model_values'));
    }
}

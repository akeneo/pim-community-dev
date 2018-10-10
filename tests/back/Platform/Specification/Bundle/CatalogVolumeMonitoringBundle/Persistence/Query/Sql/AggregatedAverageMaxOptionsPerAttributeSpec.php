<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql\AggregatedAverageMaxOptionsPerAttribute;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Prophecy\Argument;

class AggregatedAverageMaxOptionsPerAttributeSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection, 10);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AggregatedAverageMaxOptionsPerAttribute::class);
    }

    function it_is_an_average_max_query()
    {
        $this->shouldImplement(AverageMaxQuery::class);
    }

    function it_fetches_an_average_max_volume($connection, Statement $statement)
    {
        $connection->prepare(Argument::any())->willReturn($statement);

        $statement->bindValue(Argument::cetera())->shouldBeCalled();
        $statement->execute()->shouldBeCalled();
        $statement->fetch()->willReturn([
            'max'     => 12,
            'average' => 7
        ]);

        $this->fetch()->shouldBeLike(new AverageMaxVolumes(12, 7, 10, 'average_max_options_per_attribute'));
    }

    function it_fetches_a_average_max_with_empty_values_if_no_aggregated_volume_has_been_found($connection, Statement $statement)
    {
        $connection->prepare(Argument::any())->willReturn($statement);

        $statement->bindValue(Argument::cetera())->shouldBeCalled();
        $statement->execute()->shouldBeCalled();
        $statement->fetch()->willReturn(null);

        $this->fetch()->shouldBeLike(new AverageMaxVolumes(0, 0, 10, 'average_max_options_per_attribute'));
    }
}

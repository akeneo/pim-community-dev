<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql\AverageMaxProductValuesPerFamily;
use Prophecy\Argument;

class AverageMaxProductValuesPerFamilySpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection, 100);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AverageMaxProductValuesPerFamily::class);
    }

    function it_is_an_average_and_max_query()
    {
        $this->shouldImplement(AverageMaxQuery::class);
    }

    function it_gets_average_and_max_volume($connection, Statement $statement)
    {
        $connection->query(Argument::type('string'))->willReturn($statement);
        $statement->fetch()->willReturn(['average' => '5', 'max' => '10']);
        $this->fetch()->shouldBeLike(new AverageMaxVolumes(10, 5, 100, 'average_max_product_values_per_family'));
    }

    function it_gets_average_and_max_volume_of_an_empty_catalog($connection, Statement $statement)
    {
        $connection->query(Argument::type('string'))->willReturn($statement);
        $statement->fetch()->willReturn(['average' => null, 'max' => null]);

        $this->fetch()->shouldBeLike(new AverageMaxVolumes(0, 0, 100, 'average_max_product_values_per_family'));
    }
}

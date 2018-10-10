<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql\AverageMaxProductValues;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Prophecy\Argument;

class AverageMaxProductValuesSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection, 12);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AverageMaxProductValues::class);
    }

    function it_is_an_average_and_max_query()
    {
        $this->shouldImplement(AverageMaxQuery::class);
    }

    function it_gets_average_and_max_volume($connection, Statement $statement)
    {
        $connection->query(Argument::type('string'))->willReturn($statement);
        $statement->fetch()->willReturn(['average' => '4', 'max' => '10']);
        $this->fetch()->shouldBeLike(new AverageMaxVolumes(10, 4, 12, 'average_max_product_values'));
    }

    function it_gets_average_and_max_volume_of_an_empty_catalog($connection, Statement $statement)
    {
        $connection->query(Argument::type('string'))->willReturn($statement);
        $statement->fetch()->willReturn(['average' => null, 'max' => null]);

        $this->fetch()->shouldBeLike(new AverageMaxVolumes(0, 0, 12, 'average_max_product_values'));
    }
}

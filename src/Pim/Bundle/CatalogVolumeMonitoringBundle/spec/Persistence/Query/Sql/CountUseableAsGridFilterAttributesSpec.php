<?php

declare(strict_types=1);

namespace spec\Pim\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql\CountScopableAttributes;
use Pim\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql\CountUseableAsGridFilterAttributes;
use Pim\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Pim\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;
use Prophecy\Argument;

class CountUseableAsGridFilterAttributesSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection, 14);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CountUseableAsGridFilterAttributes::class);
    }

    function it_is_a_count_query()
    {
        $this->shouldImplement(CountQuery::class);
    }

    function it_gets_count_volume($connection, Statement $statement)
    {
        $connection->query(Argument::type('string'))->willReturn($statement);
        $statement->fetch()->willReturn(['count' => '7']);
        $this->fetch()->shouldBeLike(new CountVolume(7, 14, 'count_useable_as_grid_filter_attributes'));
    }
}

<?php

declare(strict_types=1);

namespace spec\Pim\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql\AggregatedCountProductValues;
use Pim\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Pim\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;
use Prophecy\Argument;

class AggregatedCountProductValuesSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection, 30);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AggregatedCountProductValues::class);
    }

    function it_is_a_count_query()
    {
        $this->shouldImplement(CountQuery::class);
    }

    function it_fetches_a_count_volume($connection, Statement $statement)
    {
        $connection->prepare(Argument::any())->willReturn($statement);

        $statement->bindValue(Argument::cetera())->shouldBeCalled();
        $statement->execute()->shouldBeCalled();
        $statement->fetch()->willReturn(['value' => 12]);

        $this->fetch()->shouldBeLike(new CountVolume(12, 30, 'count_product_values'));
    }

    function it_fetches_a_count_volume_with_an_empty_value_if_no_aggregated_volume_has_been_found($connection, Statement $statement)
    {
        $connection->prepare(Argument::any())->willReturn($statement);

        $statement->bindValue(Argument::cetera())->shouldBeCalled();
        $statement->execute()->shouldBeCalled();
        $statement->fetch()->willReturn(null);

        $this->fetch()->shouldBeLike(new CountVolume(0, 30, 'count_product_values'));
    }
}

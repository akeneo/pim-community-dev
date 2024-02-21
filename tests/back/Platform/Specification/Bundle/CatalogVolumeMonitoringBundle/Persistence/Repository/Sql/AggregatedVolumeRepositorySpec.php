<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Repository\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Repository\Sql\AggregatedVolumeRepository;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Model\AggregatedVolume;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Repository\AggregatedVolumeRepositoryInterface;
use Prophecy\Argument;

class AggregatedVolumeRepositorySpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AggregatedVolumeRepository::class);
    }

    function it_is_a_aggregated_volume_repository()
    {
        $this->shouldImplement(AggregatedVolumeRepositoryInterface::class);
    }

    function it_adds_a_aggregated_volume(
        $connection,
        AggregatedVolume $aggregatedVolume,
        Statement $statement
    ) {
        $connection->prepare(Argument::any())->willReturn($statement);

        $aggregatedVolume->getVolumeName()->willReturn('volume_name');
        $aggregatedVolume->getVolume()->willReturn(['value' => 12]);
        $aggregatedVolume->aggregatedAt()->willReturn(new \DateTime());

        $statement->bindValue(Argument::cetera())->shouldBeCalled();
        $statement->execute()->shouldBeCalled();

        $this->add($aggregatedVolume);
    }
}

<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Result;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql\AverageMaxLocalizableAttributesPerFamily;
use Prophecy\Argument;

class AverageMaxLocalizableAttributesPerFamilySpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection, 14);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AverageMaxLocalizableAttributesPerFamily::class);
    }

    function it_is_an_average_and_max_query()
    {
        $this->shouldImplement(AverageMaxQuery::class);
    }

    function it_gets_average_and_max_volume(Connection $connection, Result $statement)
    {
        $connection->executeQuery(Argument::type('string'))->willReturn($statement);
        $statement->fetchAssociative()->willReturn(['average' => '5', 'max' => '13']);
        $this->fetch()->shouldBeLike(new AverageMaxVolumes(13, 5, 'average_max_localizable_attributes_per_family'));
    }
}

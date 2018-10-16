<?php

declare(strict_types=1);

namespace Specification\Akeneo\Asset\Bundle\Persistence\Query\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;
use Akeneo\Asset\Bundle\Persistence\Query\Sql\CountAssets;
use Prophecy\Argument;

class CountAssetsSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection, 12);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CountAssets::class);
    }

    function it_is_an_average_ad_max_query()
    {
        $this->shouldImplement(CountQuery::class);
    }

    function it_gets_average_and_max_volume($connection, Statement $statement)
    {
        $connection->query(Argument::type('string'))->willReturn($statement);
        $statement->fetch()->willReturn(['count' => '4']);
        $this->fetch()->shouldBeLike(new CountVolume(4, 12, 'count_assets'));
    }
}

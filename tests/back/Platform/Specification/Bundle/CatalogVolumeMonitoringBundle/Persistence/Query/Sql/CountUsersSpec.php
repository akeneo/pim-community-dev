<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Akeneo\UserManagement\Component\Model\User;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql\CountUsers;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;
use Prophecy\Argument;

class CountUsersSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection, -1);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CountUsers::class);
    }

    function it_is_a_count_query()
    {
        $this->shouldImplement(CountQuery::class);
    }

    function it_gets_count_volume($connection, ResultStatement $statement)
    {
        $connection->executeQuery(Argument::type('string'), ['type' => User::TYPE_USER])->willReturn($statement);
        $statement->fetch()->willReturn(['count' => '25']);
        $this->fetch()->shouldBeLike(new CountVolume(25, -1, 'count_users'));
    }
}

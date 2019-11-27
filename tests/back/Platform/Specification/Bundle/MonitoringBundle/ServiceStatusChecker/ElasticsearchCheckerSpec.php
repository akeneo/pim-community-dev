<?php

namespace Specification\Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker;

use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\ElasticsearchChecker;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\ClientRegistry;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;

class ElasticsearchCheckerSpec extends ObjectBehavior
{
    function it_returns_a_ok_status_with_all_working_es_clients(ClientRegistry $clientRegistry)
    {
        $connection->ping()->willReturn(true);
        $this->beConstructedWith($connection);
        $this->shouldHaveType(MysqlChecker::class);

        $status = $this->status();
        $status->isOk()->shouldReturn(true);
        $status->getMessage()->shouldReturn("OK");
    }

    function it_returns_a_ko_status_with_a_non_working_connection(Connection $connection)
    {
        $connection->ping()->willReturn(false);
        $this->beConstructedWith($connection);
        $this->shouldHaveType(MysqlChecker::class);

        $status = $this->status();
        $status->isOk()->shouldReturn(false);
        $status->getMessage()->shouldReturn("Unable to ping the database.");
    }
}

<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\StructureVersion\Provider;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Doctrine\Persistence\ManagerRegistry;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StructureVersionSpec extends ObjectBehavior
{
    function let(ManagerRegistry $registry, Connection $connection)
    {
        $registry->getConnection()->willReturn($connection);
        $this->beConstructedWith($registry);
    }

    function it_returns_zero_when_there_is_no_version_history_record(Connection $connection, Statement $statement)
    {
        $connection->executeQuery(Argument::cetera())->willReturn($statement);
        $statement->fetchAssociative()->willReturn(false);

        $this->getStructureVersion()->shouldReturn(0);
    }

    function it_returns_the_timestamp_of_the_last_update_when_a_version_history_record_exists(
        Connection $connection,
        Statement $statement
    ) {
        $connection->executeQuery(Argument::cetera())->willReturn($statement);
        $statement->fetchAssociative()->willReturn(['last_update' => '2021-01-01 00:00:00']);
        $connection->convertToPHPValue('2021-01-01 00:00:00', 'datetime')
            ->willReturn(new \DateTime('2021-01-01 00:00:00'));

        $this->getStructureVersion()->shouldReturn((new \DateTime('2021-01-01 00:00:00'))->getTimestamp());
    }
}

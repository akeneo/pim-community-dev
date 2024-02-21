<?php

namespace spec\Akeneo\Tool\Bundle\BatchBundle\EntityManager;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;

class PersistedConnectionEntityManagerSpec extends ObjectBehavior
{
    function let(EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    function it_refreshes_connection_when_getting_connection($entityManager, Connection $connection) {
        $entityManager->getConnection()->willReturn($connection);
        $connection->ping()->willReturn(false);
        $connection->close()->shouldBeCalled();
        $connection->connect()->shouldBeCalled();

        $this->getConnection()->shouldReturn($connection);
    }

    function it_refreshes_connection_when_flushing_data($entityManager, Connection $connection) {
        $entityManager->getConnection()->willReturn($connection);
        $connection->ping()->willReturn(false);
        $connection->close()->shouldBeCalled();
        $connection->connect()->shouldBeCalled();
        $entityManager->flush(null)->shouldBeCalled();

        $this->flush();
    }
}

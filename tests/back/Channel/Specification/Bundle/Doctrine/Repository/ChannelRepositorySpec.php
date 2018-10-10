<?php

namespace Specification\Akeneo\Channel\Bundle\Doctrine\Repository;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ChannelRepositorySpec extends ObjectBehavior
{
    function let(
        EntityManager $em,
        Connection $connection,
        Statement $statement,
        ClassMetadata $classMetadata
    ) {
        $connection->prepare(Argument::any())->willReturn($statement);
        $em->getClassMetadata(Argument::any())->willReturn($classMetadata);
        $classMetadata->name = 'channel';
        $em->getConnection()->willReturn($connection);
        $this->beConstructedWith($em, $classMetadata);
    }

    function it_is_a_channel_repository()
    {
        $this->shouldImplement(ChannelRepositoryInterface::class);
    }

    function it_count_all_channels($em, QueryBuilder $queryBuilder, AbstractQuery $query)
    {
        $em->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select('c')->willReturn($queryBuilder);
        $queryBuilder->from('channel', 'c', null)->willReturn($queryBuilder);
        $queryBuilder->select('COUNT(c.id)')->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleScalarResult()->shouldBeCalled();

        $this->countAll();
    }
}

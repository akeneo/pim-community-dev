<?php

namespace Specification\Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository;

use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserRepositorySpec extends ObjectBehavior
{
    function let(
        EntityManager $em,
        Connection $connection,
        Statement $statement,
        ClassMetadata $classMetadata
    ) {
        $connection->prepare(Argument::any())->willReturn($statement);
        $em->getClassMetadata(Argument::any())->willReturn($classMetadata);
        $classMetadata->name = 'user';
        $em->getConnection()->willReturn($connection);
        $this->beConstructedWith($em, $classMetadata);
    }

    function it_is_a_user_repository()
    {
        $this->shouldHaveType(EntityRepository::class);
        $this->shouldHaveType(UserRepositoryInterface::class);
    }

    function it_get_identifier_properties()
    {
        $expected = ['username'];

        $this->getIdentifierProperties()->shouldReturn($expected);
    }

    function it_finds_one_by_identifier($em, QueryBuilder $qb, AbstractQuery $query)
    {
        $identifier = 500;

        $em->createQueryBuilder()->willReturn($qb);
        $qb->select('u')->willReturn($qb);
        $qb->from('user', 'u', null)->willReturn($qb);

        $qb->where('u.username = :identifier OR u.email = :identifier')->willReturn($qb);
        $qb->setParameter(':identifier', $identifier)->willReturn($qb);

        $qb->getQuery()->willReturn($query);
        $query->getOneOrNullResult()->shouldBeCalled();

        $this->findOneByIdentifier($identifier);
    }

    function it_finds_by_groups($em, QueryBuilder $qb, AbstractQuery $query, Expr $ex)
    {
        $groupIds = [32, 50];

        $em->createQueryBuilder()->willReturn($qb);
        $qb->select('u')->willReturn($qb);
        $qb->from('user', 'u', null)->willReturn($qb);

        $qb->expr()->willReturn($ex);
        $ex->in('g.id', $groupIds)->willReturn('IN 32, 50');
        $qb->leftJoin('u.groups', 'g')->willReturn($qb);
        $qb->where('IN 32, 50')->willReturn($qb);

        $qb->getQuery()->willReturn($query);
        $query->getResult()->shouldBeCalled();

        $this->findByGroupIds($groupIds);
    }
}

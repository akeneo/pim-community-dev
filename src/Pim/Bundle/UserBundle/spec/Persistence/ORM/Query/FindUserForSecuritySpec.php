<?php

namespace spec\Pim\Bundle\UserBundle\Persistence\ORM\Query;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\UserBundle\Persistence\ORM\Query\FindUserForSecurity;
use PhpSpec\ObjectBehavior;
use Pim\Component\User\User\ReadModel\UserForSecurity;
use Pim\Component\User\User\UserInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class FindUserForSecuritySpec extends ObjectBehavior
{
    function let(EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FindUserForSecurity::class);
    }

    function it_find_user_by_username(
        $entityManager,
        QueryBuilder $queryBuilder,
        AbstractQuery $query,
        UserForSecurity $user
    ) {
        $select = sprintf(
            'NEW %s(user.id, user.username, user.password, user.email, user.roles)',
            UserForSecurity::class
        );

        $entityManager->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select($select)->willReturn($queryBuilder);
        $queryBuilder->from(UserInterface::class, 'user', null)->willReturn($queryBuilder);
        $queryBuilder->where('user.username = :username')->willReturn($queryBuilder);
        $queryBuilder->setParameter('username', 'arnaud')->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);
        $query->getOneOrNullResult()->willReturn($user);

        $this->__invoke('arnaud')->shouldReturn($user);
    }

    function it_throws_an_exception_if_the_user_does_not_exist(
        $entityManager,
        QueryBuilder $queryBuilder,
        AbstractQuery $query
    ) {
        $select = sprintf(
            'NEW %s(user.id, user.username, user.password, user.email, user.roles)',
            UserForSecurity::class
        );

        $entityManager->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select($select)->willReturn($queryBuilder);
        $queryBuilder->from(UserInterface::class, 'user', null)->willReturn($queryBuilder);
        $queryBuilder->where('user.username = :username')->willReturn($queryBuilder);
        $queryBuilder->setParameter('username', 'arnaud')->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);
        $query->getOneOrNullResult()->willReturn(null);

        $this->shouldThrow(UsernameNotFoundException::class)->during('__invoke', ['arnaud']);
    }
}

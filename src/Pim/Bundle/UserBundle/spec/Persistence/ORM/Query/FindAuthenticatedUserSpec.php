<?php

namespace spec\Pim\Bundle\UserBundle\Persistence\ORM\Query;

use Akeneo\Component\StorageUtils\Exception\ResourceNotFoundException;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Persistence\ORM\Query\FindAuthenticatedUser;
use Pim\Component\User\Model\User;
use Pim\Component\User\ReadModel\AuthenticatedUser;

class FindAuthenticatedUserSpec extends ObjectBehavior
{
    function let(EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FindAuthenticatedUser::class);
    }

    function it_finds_user_by_username(
        $entityManager,
        QueryBuilder $userQueryBuilder,
        QueryBuilder $roleQueryBuilder,
        AbstractQuery $userQuery,
        AbstractQuery $roleQuery
    ) {
        $entityManager->createQueryBuilder()->willReturn($userQueryBuilder, $roleQueryBuilder);

        $userQueryBuilder->select('user.id, user.username, user.password, user.email, user.enabled, user.salt, uiLocale.code as uiLocaleCode, user.email')->willReturn($userQueryBuilder);
        $userQueryBuilder->from(User::class, 'user', null)->willReturn($userQueryBuilder);
        $userQueryBuilder->innerJoin('user.uiLocale', 'uiLocale')->willReturn($userQueryBuilder);
        $userQueryBuilder->where('user.username = :username')->willReturn($userQueryBuilder);
        $userQueryBuilder->setParameter('username', 'arnaud')->willReturn($userQueryBuilder);
        $userQueryBuilder->getQuery()->willReturn($userQuery);
        $userQuery->getSingleResult(AbstractQuery::HYDRATE_ARRAY)->willReturn([
            'id' => 40,
            'username' => 'username',
            'password' => 'password',
            'enabled' => true,
            'salt' => 'salt',
            'uiLocaleCode' => 'en_US',
            'email' => 'email@email.com',
        ]);

        $roleQueryBuilder->select('role.role')->willReturn($roleQueryBuilder);
        $roleQueryBuilder->from(User::class, 'user', null)->willReturn($roleQueryBuilder);
        $roleQueryBuilder->innerJoin('user.roles', 'role')->willReturn($roleQueryBuilder);
        $roleQueryBuilder->where('user.username = :username')->willReturn($roleQueryBuilder);
        $roleQueryBuilder->setParameter('username', 'arnaud')->willReturn($roleQueryBuilder);
        $roleQueryBuilder->getQuery()->willReturn($roleQuery);
        $roleQuery->getArrayResult()->willReturn([
           ['role' => 'ROLE_ADMINISTRATOR']
        ]);

        $this->__invoke('arnaud')->shouldBeLike(new AuthenticatedUser(
            40,
            'username',
            'password',
            [
                ['role' => 'ROLE_ADMINISTRATOR']
            ],
            true,
            'salt',
            'en_US',
            'email@email.com'
        ));
    }

    function it_throws_an_exception_if_the_user_does_not_exist(
        $entityManager,
        QueryBuilder $userQueryBuilder,
        AbstractQuery $userQuery
    ) {
        $entityManager->createQueryBuilder()->willReturn($userQueryBuilder);
        $userQueryBuilder->select('user.id, user.username, user.password, user.email, user.enabled, user.salt, uiLocale.code as uiLocaleCode, user.email')->willReturn($userQueryBuilder);
        $userQueryBuilder->from(User::class, 'user', null)->willReturn($userQueryBuilder);
        $userQueryBuilder->innerJoin('user.uiLocale', 'uiLocale')->willReturn($userQueryBuilder);
        $userQueryBuilder->where('user.username = :username')->willReturn($userQueryBuilder);
        $userQueryBuilder->setParameter('username', 'arnaud')->willReturn($userQueryBuilder);
        $userQueryBuilder->getQuery()->willReturn($userQuery);

        $userQuery->getSingleResult(AbstractQuery::HYDRATE_ARRAY)->willThrow(NoResultException::class);

        $this->shouldThrow(ResourceNotFoundException::class)->during('__invoke', ['arnaud']);
    }
}

<?php

declare(strict_types=1);

namespace Pim\Bundle\UserBundle\Persistence\ORM\Query;

use Akeneo\Component\StorageUtils\Exception\ResourceNotFoundException;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Pim\Component\User\Role\RoleInterface;
use Pim\Component\User\User\ReadModel\UserForSecurity;
use Pim\Component\User\User\User;
use Pim\Component\User\User\UserInterface;

/**
 * Get the data from the database to hydrate `Pim\Component\User\User\ReadModel\UserForSecurity`
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FindUserForSecurity
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(string $username): UserForSecurity
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('user.id, user.username, user.password, user.email, user.enabled, user.salt')
            ->from(User::class, 'user', null)
            ->where('user.username = :username')
            ->setParameter('username', $username)
            ->getQuery();

        try {
            $flatUser = $query->getOneOrNullResult();
        } catch (NoResultException $exception) {
            throw new ResourceNotFoundException('TODO', 0, $exception);
        }

        $query = $this->entityManager->createQueryBuilder()
            ->select('role.role')
            ->from(User::class, 'user', null)
            ->innerJoin('user.roles', 'role')
            ->where('user.username = :username')
            ->setParameter('username', $username)
            ->getQuery();
        $roles = $query->getArrayResult();

        $user =  new UserForSecurity(
            $flatUser['id'],
            $flatUser['username'],
            $flatUser['password'],
            $roles,
            $flatUser['enabled'],
            $flatUser['salt']
        );

        return $user;
    }
}

<?php

declare(strict_types=1);

namespace Pim\Bundle\UserBundle\Persistence\ORM\Query;

use Akeneo\Component\StorageUtils\Exception\ResourceNotFoundException;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Pim\Component\User\Model\User;
use Pim\Component\User\ReadModel\AuthenticatedUser;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * Get the data from the database to hydrate `Pim\Component\User\ReadModel\AuthenticatedUser`, this object represents
 * an authenticated user.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindAuthenticatedUser
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
    public function __invoke(string $username): AdvancedUserInterface
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select(
                'user.id, user.username, user.password, user.email, user.enabled, user.salt, uiLocale.code as uiLocaleCode'
            )
            ->from(User::class, 'user', null)
            ->innerJoin('user.uiLocale', 'uiLocale')
            ->where('user.username = :username')
            ->setParameter('username', $username)
            ->getQuery();

        try {
            $flatUser = $query->getSingleResult(AbstractQuery::HYDRATE_ARRAY);
        } catch (NoResultException $exception) {
            throw new ResourceNotFoundException(AuthenticatedUser::class, 0, $exception);
        }

        $query = $this->entityManager->createQueryBuilder()
            ->select('role.role')
            ->from(User::class, 'user', null)
            ->innerJoin('user.roles', 'role')
            ->where('user.username = :username')
            ->setParameter('username', $username)
            ->getQuery();
        $roles = $query->getArrayResult();

        $user =  new AuthenticatedUser(
            $flatUser['id'],
            $flatUser['username'],
            $flatUser['password'],
            $roles,
            $flatUser['enabled'],
            $flatUser['salt'],
            $flatUser['uiLocaleCode']
        );

        return $user;
    }
}

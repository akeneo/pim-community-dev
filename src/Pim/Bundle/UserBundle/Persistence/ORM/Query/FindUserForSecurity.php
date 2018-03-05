<?php

declare(strict_types=1);

namespace Pim\Bundle\UserBundle\Persistence\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\User\User\ReadModel\UserForSecurity;
use Pim\Component\User\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindUserForSecurity
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
        $select = sprintf(
            'NEW %s(user.id, user.username, user.password, user.email, user.roles)',
            UserForSecurity::class
        );

        $query = $this->entityManager->createQueryBuilder()
            ->select($select)
            ->from(UserInterface::class, 'user', null)
            ->where('user.username = :username')
            ->setParameter('username', $username)
            ->getQuery();

        if (null === $user = $query->getOneOrNullResult()) {
            throw new UsernameNotFoundException('TODO');
        }

        return $user;
    }
}

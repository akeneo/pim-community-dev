<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\Install\EventSubscriber;

use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class UpdateViewsOwnerSubscriber implements EventSubscriberInterface
{
    private Connection $dbConnection;

    private bool $isUserCreation = false;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE => 'checkIfUserCreation',
            StorageEvents::POST_SAVE => 'updateViewsOwner',
        ];
    }

    public function checkIfUserCreation(GenericEvent $event): void
    {
        $user = $event->getSubject();
        if (!$user instanceof UserInterface || $user->isApiUser()) {
            return;
        }

        $this->isUserCreation = $user->getId() === null;
    }

    public function updateViewsOwner(GenericEvent $event): void
    {
        $user = $event->getSubject();
        if (!$user instanceof UserInterface || $user->isApiUser()) {
            return;
        }

        if (!$this->isUserCreation || !$this->isFirstUser($user)) {
            return;
        }

        $query = <<<SQL
UPDATE pim_datagrid_view SET owner_id = :userId
SQL;
        $this->dbConnection->executeQuery($query, ['userId' => $user->getId()]);
    }

    public function isFirstUser(UserInterface $user): bool
    {
        $query = <<<SQL
SELECT COUNT(*) FROM oro_user WHERE user_type = :userType;
SQL;
        $usersCount = $this->dbConnection->executeQuery($query, ['userType' => User::TYPE_USER])->fetchOne();

        return 1 === intval($usersCount);
    }
}

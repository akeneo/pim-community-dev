<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\EventListener;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/** TODO Pull up to 6.0 Remove this subscriber */
class DatabaseInstallerSubscriber implements EventSubscriberInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => ['createLoginAttemptInformation'],
        ];
    }

    public function createLoginAttemptInformation(): void
    {
        $this->connection->executeUpdate('ALTER TABLE oro_user ADD consecutive_authentication_failure_counter INT DEFAULT 0');
        $this->connection->executeUpdate('ALTER TABLE oro_user ADD authentication_failure_reset_date datetime  DEFAULT NULL');
    }
}

<?php

namespace Akeneo\CoEdition\Infrastructure\Installer;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection as DbalConnection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InstallerSubscriber implements EventSubscriberInterface
{
    public function __construct(private DbalConnection $dbalConnection)
    {
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => ['updateSchema', 20],
        ];
    }

    public function updateSchema(): void
    {
        $this->dbalConnection->executeStatement(
            <<<SQL
            CREATE TABLE IF NOT EXISTS akeneo_coedition_room (
                uuid BINARY(16) NOT NULL PRIMARY KEY,
                editors JSON NOT NULL DEFAULT (JSON_ARRAY()),
                created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            SQL
        );
    }
}
{

}

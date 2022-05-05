<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Installer;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection as DbalConnection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @codeCoverageIgnore
 */
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
            InstallerEvents::POST_DB_CREATE => ['updateSchema', -20],
        ];
    }

    public function updateSchema(): void
    {
        $this->dbalConnection->executeStatement(
            <<<SQL
            CREATE TABLE IF NOT EXISTS akeneo_catalog (
                id BINARY(16) NOT NULL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            SQL
        );
    }
}

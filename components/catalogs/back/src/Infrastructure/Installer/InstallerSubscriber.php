<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Installer;

use Akeneo\Platform\Bundle\InstallerBundle\Infrastructure\Event\InstallerEvents;
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
            InstallerEvents::POST_DB_CREATE => ['updateSchema', 20],
        ];
    }

    public function updateSchema(): void
    {
        $this->dbalConnection->executeStatement(
            <<<SQL
            CREATE TABLE IF NOT EXISTS akeneo_catalog (
                id BINARY(16) NOT NULL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                owner_id INT NOT NULL,
                is_enabled TINYINT NOT NULL DEFAULT 0,
                product_selection_criteria JSON NOT NULL DEFAULT (JSON_ARRAY()),
                product_value_filters JSON NOT NULL DEFAULT (JSON_OBJECT()),
                product_mapping JSON NOT NULL DEFAULT (JSON_OBJECT()),
                created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_owner (owner_id),
                CONSTRAINT fk_owner FOREIGN KEY (owner_id) REFERENCES oro_user(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            SQL
        );
    }
}

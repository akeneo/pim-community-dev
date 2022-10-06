<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Symfony\Install;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => ['updateSchema', 20],
        ];
    }

    public function updateSchema(): void
    {
        $this->connection->executeStatement(
            <<<SQL
            CREATE TABLE IF NOT EXISTS pim_catalog_identifier_generator (
                `uuid` binary(16) PRIMARY KEY,
                `code` VARCHAR(100) NOT NULL,
                `conditions` JSON NOT NULL DEFAULT ('{}'),
                `structure` JSON NOT NULL DEFAULT ('{}'),
                `labels` JSON NOT NULL DEFAULT ('{}') ,
                `target` VARCHAR(100) NOT NULL,
                `delimiter` VARCHAR(100),
                UNIQUE INDEX unique_identifier_generator_code (code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );
    }
}

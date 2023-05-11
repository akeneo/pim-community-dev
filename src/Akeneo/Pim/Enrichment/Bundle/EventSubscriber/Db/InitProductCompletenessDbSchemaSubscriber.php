<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Db;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InitProductCompletenessDbSchemaSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'initDbSchema'
        ];
    }

    public function initDbSchema(InstallerEvent $event): void
    {
        $completenessTableSql = <<<SQL
            CREATE TABLE IF NOT EXISTS pim_catalog_product_completeness(
                `product_uuid` binary(16) NOT NULL,
                `completeness` JSON NOT NULL DEFAULT (JSON_OBJECT()),
                PRIMARY KEY (`product_uuid`),
                CONSTRAINT `FK_PRODUCTUUID_COMPLETENESS` FOREIGN KEY (`product_uuid`) REFERENCES `pim_catalog_product` (`uuid`) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
SQL;
        $this->connection->executeStatement($completenessTableSql);
    }
}

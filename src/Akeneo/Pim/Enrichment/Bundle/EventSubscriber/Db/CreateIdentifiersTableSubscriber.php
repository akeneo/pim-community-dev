<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Db;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateIdentifiersTableSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'createIdentifiersTable'
        ];
    }

    public function createIdentifiersTable(): void
    {
        $this->connection->executeStatement(
            <<<SQL
            CREATE TABLE IF NOT EXISTS pim_catalog_product_identifiers(
                uuid BINARY(16) NOT NULL PRIMARY KEY,
                identifiers JSON DEFAULT NULL COMMENT '(DC2Type:json_array)',
                CONSTRAINT pim_catalog_product_identifiers_pim_catalog_product_uuid_fk
                    FOREIGN KEY (uuid) REFERENCES pim_catalog_product (uuid)
                        ON DELETE CASCADE,
                INDEX idx_identifiers ( (CAST(identifiers AS CHAR(255) array)) )
            )
            SQL
        );
    }
}

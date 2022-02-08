<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Db;

use _PHPStan_76800bfb5\Nette\Neon\Exception;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * We have to temporarily add foreign columns "product_uuid".
 * For basic Doctrine models, this field has been added directly in the Doctrine configuration.
 * In association tables, we can not add this field, so we need a dedicated subscriber.
 *
 * @see upgrades/schema/Version_7_0_20220208140602_add_product_uuid_and_foreign_uuids.php
 */
class InitAssociationTablesWithProductUuid implements EventSubscriberInterface
{
    private $connection;

    private const FOREIGN_TABLE_NAMES_AND_COLUMN_NAMES = [
        'pim_catalog_association_product' => 'product_id',
        'pim_catalog_association_product_model_to_product' => 'product_id',
        'pim_catalog_category_product' => 'product_id',
        'pim_catalog_group_product' => 'product_id',
    ];

    public function __construct(Connection $dbalConnection)
    {
        $this->connection = $dbalConnection;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'initDbSchema'
        ];
    }

    public function initDbSchema(InstallerEvent $event): void
    {
        foreach (self::FOREIGN_TABLE_NAMES_AND_COLUMN_NAMES as $foreignTable => $idColumnName) {
            $uuidColumnName = str_replace('_id', '_uuid', $idColumnName);
            $this->addColumn($foreignTable, $uuidColumnName, $idColumnName);
        }
    }

    private function addColumn(string $tableName, string $uuidColumnName, string $idColumnName): void
    {
        $addForeignProductUuid = sprintf(
            'ALTER TABLE `%s` ADD `%s` VARBINARY(16) DEFAULT NULL AFTER `%s`, LOCK=NONE, ALGORITHM=INPLACE;',
            $tableName,
            $uuidColumnName,
            $idColumnName
        );

        $this->connection->executeQuery($addForeignProductUuid);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Symfony\Install;

use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents;
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
                `target_id` INT NOT NULL,
                `options` JSON NOT NULL DEFAULT('{}'),
                `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                UNIQUE INDEX unique_identifier_generator_code (code),
                KEY `target_id` (`target_id`),
                CONSTRAINT `pim_catalog_identifier_generator_ibfk_1` FOREIGN KEY (`target_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );
        $this->connection->executeStatement(
            <<<SQL
            CREATE TABLE IF NOT EXISTS pim_catalog_identifier_generator_prefixes (
                `product_uuid` binary(16) NOT NULL,
                `attribute_id` INT NOT NULL,
                `prefix` VARCHAR(255) NOT NULL,
                `number` BIGINT UNSIGNED NOT NULL,
                CONSTRAINT `FK_PRODUCTUUID` FOREIGN KEY (`product_uuid`) REFERENCES `pim_catalog_product` (`uuid`) ON DELETE CASCADE,
                CONSTRAINT `FK_ATTRIBUTEID` FOREIGN KEY (`attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE,
                INDEX index_identifier_generator_prefixes (`attribute_id`, `prefix`, `number`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );
        $this->connection->executeStatement(
            <<<SQL
            CREATE TABLE IF NOT EXISTS pim_catalog_identifier_generator_sequence (
                `attribute_id` INT NOT NULL,
                `identifier_generator_uuid` binary(16) NOT NULL,
                `prefix` VARCHAR(255) NOT NULL,
                `last_allocated_number` BIGINT UNSIGNED NOT NULL,
                CONSTRAINT `FK_SEQ_ATTRIBUTEID` FOREIGN KEY (`attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE,
                CONSTRAINT `FK_SEQ_IDENTIFIERGENERATORUUID` FOREIGN KEY (`identifier_generator_uuid`) REFERENCES `pim_catalog_identifier_generator` (`uuid`) ON DELETE CASCADE,
                UNIQUE INDEX sequence_attribute_identifier_prefix (attribute_id, identifier_generator_uuid, prefix),
                INDEX index_identifier_generator_sequence (`attribute_id`, `identifier_generator_uuid`, `prefix`, `last_allocated_number`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );
        $this->connection->executeStatement(
            <<<SQL
            CREATE TABLE IF NOT EXISTS pim_catalog_identifier_generator_nomenclature_definition (
                `property_code` VARCHAR(255) NOT NULL,
                `definition` JSON NOT NULL DEFAULT ('{}'),
                UNIQUE INDEX nomenclature_definition_property_code (`property_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );
        $this->connection->executeStatement(
            <<<SQL
            CREATE TABLE IF NOT EXISTS pim_catalog_identifier_generator_family_nomenclature (
                `family_id` INT NOT NULL,
                `value` VARCHAR(255) NOT NULL,
                UNIQUE INDEX family_nomenclature_family_id (`family_id`),
                CONSTRAINT `FK_FAMILY_NOMENCLATURE` FOREIGN KEY (`family_id`) REFERENCES `pim_catalog_family` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );
        $this->connection->executeStatement(
            <<<SQL
            CREATE TABLE IF NOT EXISTS pim_catalog_identifier_generator_simple_select_nomenclature (
                `option_id` INT NOT NULL,
                `value` VARCHAR(255) NOT NULL,
                UNIQUE INDEX simple_select_nomenclature_option_id (`option_id`),
                CONSTRAINT `FK_SIMPLE_SELECT_NOMENCLATURE` FOREIGN KEY (`option_id`) REFERENCES `pim_catalog_attribute_option` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * The Product completeness is a projection of the Product. Since 4.0, it's no more attached to the product with
 * Doctrine configuration.
 * We need to manually create the tables to save the completeness in database.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InitCompletenessDbSchemaSubscriber implements EventSubscriberInterface
{
    private $connection;

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
        $completenessTableSql = "CREATE TABLE `pim_catalog_completeness` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `locale_id` int(11) NOT NULL,
            `channel_id` int(11) NOT NULL,
            `product_id` int(11) NOT NULL,
            `ratio` int(11) NOT NULL,
            `missing_count` int(11) NOT NULL,
            `required_count` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `searchunique_idx` (`channel_id`,`locale_id`,`product_id`),
            KEY `IDX_113BA854E559DFD1` (`locale_id`),
            KEY `IDX_113BA85472F5A1AA` (`channel_id`),
            KEY `IDX_113BA8544584665A` (`product_id`),
            KEY `ratio_idx` (`ratio`),
            CONSTRAINT `FK_113BA8544584665A` FOREIGN KEY (`product_id`) REFERENCES `pim_catalog_product` (`id`) ON DELETE CASCADE,
            CONSTRAINT `FK_113BA85472F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `pim_catalog_channel` (`id`) ON DELETE CASCADE,
            CONSTRAINT `FK_113BA854E559DFD1` FOREIGN KEY (`locale_id`) REFERENCES `pim_catalog_locale` (`id`) ON DELETE CASCADE
        )";
        $this->connection->exec($completenessTableSql);

        $missingTableSql = "CREATE TABLE `pim_catalog_completeness_missing_attribute` (
            `completeness_id` int(11) NOT NULL,
            `missing_attribute_id` int(11) NOT NULL,
            PRIMARY KEY (`completeness_id`,`missing_attribute_id`),
            KEY `IDX_DF61BBD3B640FBA5` (`completeness_id`),
            KEY `IDX_DF61BBD3762147F6` (`missing_attribute_id`),
            CONSTRAINT `FK_DF61BBD3762147F6` FOREIGN KEY (`missing_attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE,
            CONSTRAINT `FK_DF61BBD3B640FBA5` FOREIGN KEY (`completeness_id`) REFERENCES `pim_catalog_completeness` (`id`) ON DELETE CASCADE
        )";
        $this->connection->exec($missingTableSql);
    }
}

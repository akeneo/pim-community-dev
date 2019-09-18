<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class CreatePublishedProductCompletenessTablesSubscriber implements EventSubscriberInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'createPublishedCompletenessTables',
        ];
    }

    public function createPublishedCompletenessTables(InstallerEvent $event): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `pimee_workflow_published_product_completeness`
(
    `id`             int(11) NOT NULL AUTO_INCREMENT,
    `locale_id`      int(11) NOT NULL,
    `channel_id`     int(11) NOT NULL,
    `product_id`     int(11) NOT NULL,
    `ratio`          int(11) NOT NULL,
    `missing_count`  int(11) NOT NULL,
    `required_count` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `searchunique_idx` (`channel_id`, `locale_id`, `product_id`),
    KEY `IDX_AA5F1874E559DFD1` (`locale_id`),
    KEY `IDX_AA5F187472F5A1AA` (`channel_id`),
    KEY `IDX_AA5F18744584665A` (`product_id`),
    KEY `ratio_idx` (`ratio`),
    CONSTRAINT `FK_AA5F18744584665A` FOREIGN KEY (`product_id`) REFERENCES `pimee_workflow_published_product` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_AA5F187472F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `pim_catalog_channel` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_AA5F1874E559DFD1` FOREIGN KEY (`locale_id`) REFERENCES `pim_catalog_locale` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `pimee_workflow_published_product_completeness_missing_attribute`
(
    `completeness_id`      int(11) NOT NULL,
    `missing_attribute_id` int(11) NOT NULL,
    PRIMARY KEY (`completeness_id`, `missing_attribute_id`),
    KEY `IDX_B0FD5518B640FBA5` (`completeness_id`),
    KEY `IDX_B0FD5518762147F6` (`missing_attribute_id`),
    CONSTRAINT `FK_B0FD5518762147F6` FOREIGN KEY (`missing_attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE CASCADE,
    CONSTRAINT `FK_B0FD5518B640FBA5` FOREIGN KEY (`completeness_id`) REFERENCES `pimee_workflow_published_product_completeness` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
SQL;

        $this->connection->exec($sql);
    }
}

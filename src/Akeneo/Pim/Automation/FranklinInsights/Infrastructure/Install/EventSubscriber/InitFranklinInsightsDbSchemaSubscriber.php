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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Install\EventSubscriber;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class InitFranklinInsightsDbSchemaSubscriber implements EventSubscriberInterface
{
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'initDbSchema'
        ];
    }

    public function initDbSchema(InstallerEvent $event): void
    {
        $this->initAttributeCreatedTable();
        $this->initAttributeAddedToFamilyTable();
    }

    private function initAttributeCreatedTable(): void
    {
        $sqlQuery = <<<'SQL'
CREATE TABLE IF NOT EXISTS pimee_franklin_insights_attribute_created(
    attribute_code VARCHAR(100) NOT NULL,
    attribute_type VARCHAR(255) NOT NULL,
    created DATETIME NOT NULL COMMENT '(DC2Type:datetime)' DEFAULT CURRENT_TIMESTAMP, 
    INDEX IDX_FI_AATF_attribute_code (attribute_code)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
SQL;
        $this->dbalConnection->executeQuery($sqlQuery);
    }

    private function initAttributeAddedToFamilyTable(): void
    {
        $sqlQuery = <<<'SQL'
CREATE TABLE IF NOT EXISTS pimee_franklin_insights_attribute_added_to_family(
    attribute_code VARCHAR(100) NOT NULL,
    family_code VARCHAR(100) NOT NULL,
    created DATETIME NOT NULL COMMENT '(DC2Type:datetime)' DEFAULT CURRENT_TIMESTAMP, 
    INDEX IDX_FI_aatf_attribute_code (attribute_code),
    INDEX IDX_FI_aatf_family_code (family_code)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
SQL;
        $this->dbalConnection->executeQuery($sqlQuery);
    }
}

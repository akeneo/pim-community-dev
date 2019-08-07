<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\EventListener;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listen to the database installation event to create the tables needed for the catalog volume monitoring.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallDatabase implements EventSubscriberInterface
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'createAggregatedVolumeTable',
        ];
    }

    public function createAggregatedVolumeTable(): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS pim_aggregated_volume (
  volume_name VARCHAR(255) NOT NULL,
  volume json NOT NULL,
  aggregated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime)',
  PRIMARY KEY(volume_name)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
SQL;

        $this->connection->exec($sql);
    }
}

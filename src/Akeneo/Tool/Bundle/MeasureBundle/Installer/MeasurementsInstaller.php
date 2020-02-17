<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Installer;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * {description}
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasurementsInstaller implements EventSubscriberInterface
{
    /** @var Connection */
    private $connection;

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
            InstallerEvents::POST_DB_CREATE => ['createSchema'],
            InstallerEvents::POST_LOAD_FIXTURES => ['loadFixtures'],
        ];
    }

    public function createSchema(): void
    {
        $sql = <<<SQL
CREATE TABLE `akeneo_measurement` (
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE,
  `standard_unit` varchar(100) NOT NULL COMMENT '(DC2Type:datetime)',
  `root` int NOT NULL,
  `lvl` int NOT NULL,
  `lft` int NOT NULL,
  `rgt` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pim_category_code_uc` (`code`),
  KEY `IDX_350D8339727ACA70` (`parent_id`),
  KEY `left_idx` (`lft`),
) ENGINE=InnoDB AUTO_INCREMENT=169 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
SQL;
        $this->connection->exec($sql);
    }

    public function loadFixtures(InstallerEvent $event): void
    {

    }
}

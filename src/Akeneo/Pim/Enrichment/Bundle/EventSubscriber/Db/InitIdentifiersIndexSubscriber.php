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
final class InitIdentifiersIndexSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'initIdentifiersIndex'
        ];
    }

    public function initIdentifiersIndex(): void
    {
        $this->connection->executeStatement(
            'ALTER TABLE pim_catalog_product ADD INDEX idx_identifiers ( (CAST(identifiers AS CHAR(255) ARRAY)) )'
        );
    }
}

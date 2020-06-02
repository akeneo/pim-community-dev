<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Install;

use Akeneo\Connectivity\Connection\Infrastructure\Install\Query\CreateConnectionAuditErrorTableQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Install\Query\CreateConnectionAuditTableQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Install\Query\CreateConnectionTableQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Install\Query\CreateWrongCredentialsCombinationQuery;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Driver\Connection as DbalConnection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InstallSubscriber implements EventSubscriberInterface
{
    private $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_DB_CREATE => ['createConnectionsTable'],
        ];
    }

    public function createConnectionsTable(): void
    {
        $this->dbalConnection->exec(CreateConnectionTableQuery::QUERY);
        $this->dbalConnection->exec(CreateConnectionAuditTableQuery::QUERY);
        $this->dbalConnection->exec(CreateWrongCredentialsCombinationQuery::QUERY);
        $this->dbalConnection->exec(CreateConnectionAuditErrorTableQuery::QUERY);
    }
}

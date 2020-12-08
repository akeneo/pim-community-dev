<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Install;

use Akeneo\Connectivity\Connection\Infrastructure\Install\Query\CreateConnectionAuditErrorTableQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Install\Query\CreateConnectionAuditTableQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Install\Query\CreateConnectionEventsApiRequestCountTableQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Install\Query\CreateConnectionTableQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Install\Query\CreateWrongCredentialsCombinationQuery;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection as DbalConnection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InstallSubscriber implements EventSubscriberInterface
{
    const ICECAT_DEMO_DEV = 'icecat_demo_dev';

    private DbalConnection $dbalConnection;
    private FixturesLoader $fixturesLoader;

    public function __construct(DbalConnection $dbalConnection, FixturesLoader $fixturesLoader)
    {
        $this->dbalConnection = $dbalConnection;
        $this->fixturesLoader = $fixturesLoader;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'createConnectionsTable',
            InstallerEvents::POST_LOAD_FIXTURES => 'loadFixtures'
        ];
    }

    public function createConnectionsTable(): void
    {
        $this->dbalConnection->exec(CreateConnectionTableQuery::QUERY);
        $this->dbalConnection->exec(CreateConnectionAuditTableQuery::QUERY);
        $this->dbalConnection->exec(CreateWrongCredentialsCombinationQuery::QUERY);
        $this->dbalConnection->exec(CreateConnectionAuditErrorTableQuery::QUERY);
        $this->dbalConnection->exec(CreateConnectionEventsApiRequestCountTableQuery::QUERY);
    }

    public function loadFixtures(InstallerEvent $installerEvent): void
    {
        if (substr($installerEvent->getArgument('catalog'), -strlen(self::ICECAT_DEMO_DEV)) !== self::ICECAT_DEMO_DEV) {
            return;
        }

        $this->fixturesLoader->loadFixtures();
    }
}

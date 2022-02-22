<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Install;

use Akeneo\Connectivity\Connection\Application\Apps\Command\GenerateAsymmetricKeysCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\GenerateAsymmetricKeysHandler;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Install\CreateAppTableQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Install\Query\CreateConnectionAuditErrorTableQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Install\Query\CreateConnectionAuditTableQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Install\Query\CreateConnectionEventsApiRequestCountTableQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Install\Query\CreateConnectionTableQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Install\Query\CreateTestAppTableQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Install\Query\CreateUserConsentTable;
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
    private GenerateAsymmetricKeysHandler $generateAsymmetricKeysHandler;

    public function __construct(DbalConnection $dbalConnection, FixturesLoader $fixturesLoader, GenerateAsymmetricKeysHandler $generateAsymmetricKeysHandler)
    {
        $this->dbalConnection = $dbalConnection;
        $this->fixturesLoader = $fixturesLoader;
        $this->generateAsymmetricKeysHandler = $generateAsymmetricKeysHandler;
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
        $this->dbalConnection->executeStatement(CreateConnectionTableQuery::QUERY);
        $this->dbalConnection->executeStatement(CreateConnectionAuditTableQuery::QUERY);
        $this->dbalConnection->executeStatement(CreateWrongCredentialsCombinationQuery::QUERY);
        $this->dbalConnection->executeStatement(CreateConnectionAuditErrorTableQuery::QUERY);
        $this->dbalConnection->executeStatement(CreateConnectionEventsApiRequestCountTableQuery::QUERY);
        $this->dbalConnection->executeStatement(CreateAppTableQuery::QUERY);
        $this->dbalConnection->executeStatement(CreateUserConsentTable::QUERY);
        $this->dbalConnection->executeStatement(CreateTestAppTableQuery::QUERY);
    }

    public function loadFixtures(InstallerEvent $installerEvent): void
    {
        $this->addOpenIdKeys();

        if (substr($installerEvent->getArgument('catalog'), -strlen(self::ICECAT_DEMO_DEV)) !== self::ICECAT_DEMO_DEV) {
            return;
        }

        $this->fixturesLoader->loadFixtures();
    }

    private function addOpenIdKeys(): void
    {
        $this->generateAsymmetricKeysHandler->handle(new GenerateAsymmetricKeysCommand());
    }
}

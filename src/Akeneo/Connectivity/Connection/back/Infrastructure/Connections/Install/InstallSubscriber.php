<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Connections\Install;

use Akeneo\Connectivity\Connection\Infrastructure\Connections\WrongCredentialsCombination\Install\CreateWrongCredentialsCombinationQuery;
use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvent;
use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents;
use Doctrine\DBAL\Connection as DbalConnection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InstallSubscriber implements EventSubscriberInterface
{
    const ICECAT_DEMO_DEV = 'icecat_demo_dev';

    public function __construct(
        private DbalConnection $dbalConnection,
        private FixturesLoader $fixturesLoader,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => ['updateSchema', 10],
            InstallerEvents::POST_LOAD_FIXTURES => ['loadFixtures', -10],
        ];
    }

    public function updateSchema(): void
    {
        $this->dbalConnection->executeStatement(CreateConnectionTableQuery::QUERY);
        $this->dbalConnection->executeStatement(CreateWrongCredentialsCombinationQuery::QUERY);
    }

    public function loadFixtures(InstallerEvent $installerEvent): void
    {
        if (!\str_ends_with($installerEvent->getArgument('catalog'), self::ICECAT_DEMO_DEV)) {
            return;
        }

        $this->fixturesLoader->loadFixtures();
    }
}

<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Install;

use Akeneo\Connectivity\Connection\Infrastructure\Install\Query\CreateConnectionAuditTableQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Install\Query\CreateConnectionTableQuery;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Driver\Connection as DbalConnection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InstallSubscriber implements EventSubscriberInterface
{
    private $assetsInstaller;
    private $dbalConnection;

    public function __construct(AssetsInstaller $assetsInstaller, DbalConnection $dbalConnection)
    {
        $this->assetsInstaller = $assetsInstaller;
        $this->dbalConnection = $dbalConnection;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_DB_CREATE => ['createConnectionsTable'],
            InstallerEvents::POST_SYMFONY_ASSETS_DUMP => ['installAssets'],
            InstallerEvents::POST_ASSETS_DUMP => ['installAssets']
        ];
    }

    public function installAssets(GenericEvent $event): void
    {
        $shouldSymlink = $event->getArgument('symlink');
        $this->assetsInstaller->installAssets($shouldSymlink);
    }

    public function createConnectionsTable(): void
    {
        $this->dbalConnection->exec(CreateConnectionTableQuery::QUERY);
        $this->dbalConnection->exec(CreateConnectionAuditTableQuery::QUERY);
    }
}

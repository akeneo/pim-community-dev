<?php
declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Install;

use Akeneo\Apps\Infrastructure\Install\Query\CreateAppAuditTableQuery;
use Akeneo\Apps\Infrastructure\Install\Query\CreateAppsTableQuery;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Driver\Connection;
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

    public function __construct(AssetsInstaller $assetsInstaller, Connection $dbalConnection)
    {
        $this->assetsInstaller = $assetsInstaller;
        $this->dbalConnection = $dbalConnection;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_DB_CREATE => ['createAppsTable'],
            InstallerEvents::POST_SYMFONY_ASSETS_DUMP => ['installAssets'],
            InstallerEvents::POST_ASSETS_DUMP => ['installAssets']
        ];
    }

    public function installAssets(GenericEvent $event): void
    {
        $shouldSymlink = $event->getArgument('symlink');
        $this->assetsInstaller->installAssets($shouldSymlink);
    }

    public function createAppsTable(GenericEvent $event): void
    {
        $this->dbalConnection->exec(CreateAppsTableQuery::QUERY);
        $this->dbalConnection->exec(CreateAppAuditTableQuery::QUERY);
    }
}

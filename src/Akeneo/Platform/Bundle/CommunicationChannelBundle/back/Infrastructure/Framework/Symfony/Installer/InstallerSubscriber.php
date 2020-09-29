<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Infrastructure\Framework\Symfony\Installer;

use Akeneo\Platform\Bundle\CommunicationChannelBundle\back\Infrastructure\Framework\Symfony\Installer\AssetsInstaller;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Platform\CommunicationChannel\Infrastructure\Framework\Symfony\Installer\Query\CreateViewedAnnouncementsTableQuery;
use Doctrine\DBAL\Connection as DbalConnection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallerSubscriber implements EventSubscriberInterface
{
    private $dbalConnection;

    private $assetsInstaller;

    public function __construct(DbalConnection $dbalConnection, AssetsInstaller $assetsInstaller)
    {
        $this->dbalConnection = $dbalConnection;
        $this->assetsInstaller = $assetsInstaller;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_DB_CREATE => ['createCommunicationChannelTable'],
            InstallerEvents::POST_ASSETS_DUMP => ['installAssets'],
        ];
    }

    public function installAssets(GenericEvent $event): void
    {
        $shouldSymlink = $event->getArgument('symlink');
        $this->assetsInstaller->installAssets($shouldSymlink);
    }

    public function createCommunicationChannelTable(): void
    {
        $this->dbalConnection->exec(CreateViewedAnnouncementsTableQuery::QUERY);
    }
}

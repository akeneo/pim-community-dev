<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CommunicationChannelBundle\back\Infrastructure\Framework\Symfony\Installer;

use Akeneo\Platform\Bundle\CommunicationChannelBundle\back\Infrastructure\Framework\Symfony\Installer\AssetsInstaller;
use Akeneo\Platform\Bundle\CommunicationChannelBundle\back\Infrastructure\Framework\Symfony\Installer\FrontendDependencies;
use Akeneo\Platform\Bundle\CommunicationChannelBundle\back\Infrastructure\Framework\Symfony\Installer\Query\CreateViewedAnnouncementsTableQuery;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
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

    private $frontendDependencies;

    public function __construct(DbalConnection $dbalConnection, AssetsInstaller $assetsInstaller, FrontendDependencies $frontendDependencies)
    {
        $this->dbalConnection = $dbalConnection;
        $this->assetsInstaller = $assetsInstaller;
        $this->frontendDependencies = $frontendDependencies;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_DB_CREATE => ['createCommunicationChannelTable'],
            InstallerEvents::POST_ASSETS_DUMP => [['installAssets'], ['addRequiredDependencies']],
        ];
    }

    public function installAssets(GenericEvent $event): void
    {
        $shouldSymlink = $event->getArgument('symlink');
        $this->assetsInstaller->installAssets($shouldSymlink);
    }

    public function addRequiredDependencies(GenericEvent $event): void
    {
        $this->frontendDependencies->addRequiredDependencies();
    }

    public function createCommunicationChannelTable(): void
    {
        $this->dbalConnection->exec(CreateViewedAnnouncementsTableQuery::QUERY);
    }
}

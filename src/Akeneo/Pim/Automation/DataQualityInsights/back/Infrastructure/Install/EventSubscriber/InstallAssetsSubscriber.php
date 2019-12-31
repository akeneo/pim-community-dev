<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install\EventSubscriber;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class InstallAssetsSubscriber implements EventSubscriberInterface
{
    private $assetsInstaller;
    private $dbalConnection;

    public function __construct(FileSystemAssetsInstaller $assetsInstaller, Connection $dbalConnection)
    {
        $this->assetsInstaller = $assetsInstaller;
        $this->dbalConnection = $dbalConnection;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_SYMFONY_ASSETS_DUMP => ['installAssets'],
            InstallerEvents::POST_ASSETS_DUMP => ['installAssets']
        ];
    }

    public function installAssets(GenericEvent $event): void
    {
        $shouldSymlink = $event->getArgument('symlink');
        $this->assetsInstaller->installAssets($shouldSymlink);
    }
}

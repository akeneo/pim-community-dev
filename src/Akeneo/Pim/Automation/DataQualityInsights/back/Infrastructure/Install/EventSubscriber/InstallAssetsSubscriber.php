<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install\EventSubscriber;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Platform\Bundle\InstallerBundle\Event\Subscriber\AssetsInstaller;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class InstallAssetsSubscriber implements EventSubscriberInterface
{
    private AssetsInstaller $assetsInstaller;

    public function __construct(AssetsInstaller $assetsInstaller)
    {
        $this->assetsInstaller = $assetsInstaller;
    }

    public static function getSubscribedEvents(): array
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

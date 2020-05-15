<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Install;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InstallSubscriber implements EventSubscriberInterface
{
    private $assetsInstaller;

    public function __construct(AssetsInstaller $assetsInstaller)
    {
        $this->assetsInstaller = $assetsInstaller;
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

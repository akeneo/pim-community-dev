<?php

namespace Akeneo\Apps\Infrastructure\EventSubscriber;

use Akeneo\Apps\Infrastructure\Installer\AssetsInstaller;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InstallerSubscriber implements EventSubscriberInterface
{
    /** @var AssetsInstaller */
    private $assetInstaller;

    public function __construct(AssetsInstaller $assetInstaller) {
        $this->assetInstaller = $assetInstaller;
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
        $this->assetInstaller->installAssets($shouldSymlink);
    }
}

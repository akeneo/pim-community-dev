<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\Install\EventSubscriber;

use Akeneo\FreeTrial\Infrastructure\Install\InstallCatalogTrait;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExtractCatalogSubscriber implements EventSubscriberInterface
{
    use InstallCatalogTrait;

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'extractCatalogOnDbCreate'
        ];
    }

    public function extractCatalogOnDbCreate(InstallerEvent $event): void
    {
        if (!$this->isFreeTrialCatalogInstallation($event)) {
            return;
        }

        $event->getCommandExecutor()->runCommand('akeneo:free-trial:extract-catalog');
    }
}

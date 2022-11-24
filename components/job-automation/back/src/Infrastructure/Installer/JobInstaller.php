<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Infrastructure\Installer;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Platform\JobAutomation\Application\GenerateAsymmetricKeys\GenerateAsymmetricKeysHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JobInstaller implements EventSubscriberInterface
{
    public function __construct(
        private GenerateAsymmetricKeysHandler $generateAsymmetricKeysHandler,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_LOAD_FIXTURES => ['loadFixtures'],
        ];
    }

    public function loadFixtures(): void
    {
        $this->generateAsymmetricKeysHandler->handle();
    }
}

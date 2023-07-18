<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\EventSubscriber;


use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Platform\Installer\Application\UpdateMaintenanceMode\UpdateMaintenanceModeCommand;
use Akeneo\Platform\Installer\Application\UpdateMaintenanceMode\UpdateMaintenanceModeHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MaintenanceModeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UpdateMaintenanceModeHandler $updateMaintenanceModeHandler,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::PRE_RESET_INSTANCE => 'enableMaintenanceMode',
            InstallerEvents::POST_RESET_INSTANCE => 'disableMaintenanceMode',
        ];
    }

    public function enableMaintenanceMode(): void
    {
        $this->updateMaintenanceModeHandler->handle(
            new UpdateMaintenanceModeCommand(true),
        );
    }

    public function disableMaintenanceMode(): void
    {
        $this->updateMaintenanceModeHandler->handle(
            new UpdateMaintenanceModeCommand(false),
        );
    }
}

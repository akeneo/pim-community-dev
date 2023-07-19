<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\EventSubscriber;


use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Platform\Installer\Application\IsMaintenanceModeEnabled\IsMaintenanceModeEnabledHandler;
use Akeneo\Platform\Installer\Application\UpdateMaintenanceMode\UpdateMaintenanceModeCommand;
use Akeneo\Platform\Installer\Application\UpdateMaintenanceMode\UpdateMaintenanceModeHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class MaintenanceModeSubscriber implements EventSubscriberInterface
{
    public function __construct(
//        private readonly FeatureFlag $sandbox,
        private readonly IsMaintenanceModeEnabledHandler $isMaintenanceModeEnabledHandler,
        private readonly UpdateMaintenanceModeHandler $updateMaintenanceModeHandler,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'redirectToMaintenanceLandingPage',
            InstallerEvents::PRE_RESET_INSTANCE => 'enableMaintenanceMode',
            InstallerEvents::POST_RESET_INSTANCE => 'disableMaintenanceMode',
        ];
    }

    public function redirectToMaintenanceLandingPage(RequestEvent $event): void
    {
//        if ($this->sandbox->isEnabled() && $this->isMaintenanceModeEnabledHandler->handle()) {
//            $event->setResponse(new RedirectResponse('maintenance'));
//        }
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

<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\EventSubscriber;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents;
use Akeneo\Platform\Installer\Application\IsMaintenanceModeEnabled\IsMaintenanceModeEnabledHandler;
use Akeneo\Platform\Installer\Application\UpdateMaintenanceMode\UpdateMaintenanceModeCommand;
use Akeneo\Platform\Installer\Application\UpdateMaintenanceMode\UpdateMaintenanceModeHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class MaintenanceModeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly FeatureFlag $pimResetFeatureFlag,
        private readonly RouterInterface $router,
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
        if ('akeneo_installer_maintenance_page' === $event->getRequest()->attributes->get('_route')) {
            return;
        }

        if ($this->pimResetFeatureFlag->isEnabled()
            && $this->isMaintenanceModeEnabledHandler->handle()
        ) {
            if ($this->isApiRequest($event->getRequest())) {
                $event->setResponse(new Response('Undergoing maintenance: this PIM instance is being reset.', Response::HTTP_SERVICE_UNAVAILABLE));
            } else {
                $event->setResponse(new RedirectResponse($this->router->generate('akeneo_installer_maintenance_page')));
            }
        }
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

    private function isApiRequest(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api/');
    }
}

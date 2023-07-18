<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Controller;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Platform\Installer\Application\ResetInstance\ResetInstanceCommand;
use Akeneo\Platform\Installer\Application\ResetInstance\ResetInstanceHandler;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ResetInstanceAction
{
    public function __construct(
        private readonly ResetInstanceHandler $resetInstanceHandler,
        private readonly SecurityFacade $securityFacade,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        if (!$this->securityFacade->isGranted('pim_reset_instance')) {
            throw new AccessDeniedException();
        }

        $this->eventDispatcher->dispatch(new InstallerEvent(), InstallerEvents::PRE_RESET_INSTANCE);
        $this->resetInstanceHandler->handle(new ResetInstanceCommand());
        $this->eventDispatcher->dispatch(new InstallerEvent(), InstallerEvents::POST_RESET_INSTANCE);

        return new JsonResponse();
    }
}

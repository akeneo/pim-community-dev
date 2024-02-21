<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Public;

use Akeneo\Connectivity\Connection\Application\Apps\Command\FlagAppContainingOutdatedScopesCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\FlagAppContainingOutdatedScopesHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByUserIdentifierQueryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FlagConnectedAppWithOutdatedScopesAction
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private FindOneConnectedAppByUserIdentifierQueryInterface $findOneConnectedAppByUserIdentifierQuery,
        private FlagAppContainingOutdatedScopesHandler $flagAppContainingOutdatedScopesHandler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        if (null === $user) {
            throw new AccessDeniedHttpException('Not an authenticated App');
        }

        if (!$user instanceof UserInterface) {
            throw new \LogicException();
        }

        $connectedApp = $this->findOneConnectedAppByUserIdentifierQuery->execute($user->getUserIdentifier());
        if (null === $connectedApp) {
            throw new AccessDeniedHttpException('Not an authenticated App');
        }

        $this->flagAppContainingOutdatedScopesHandler->handle(new FlagAppContainingOutdatedScopesCommand(
            $connectedApp,
            $request->query->get('scopes', ''),
        ));

        return new JsonResponse('Ok');
    }
}

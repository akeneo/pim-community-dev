<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Apps\ConnectedPimUserProviderInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByConnectionCodeQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetUserConsentedAuthenticationScopesQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetConnectedAppAuthenticationScopesAction
{
    public function __construct(
        private GetUserConsentedAuthenticationScopesQueryInterface $getUserConsentedAuthenticationScopesQuery,
        private FindOneConnectedAppByConnectionCodeQueryInterface $findOneConnectedAppByConnectionCodeQuery,
        private ConnectedPimUserProviderInterface $connectedPimUserProvider,
    ) {
    }

    public function __invoke(string $connectionCode): Response
    {
        $connectedApp = $this->findOneConnectedAppByConnectionCodeQuery->execute($connectionCode);
        if (null === $connectedApp) {
            throw new NotFoundHttpException("Connected app with connection code $connectionCode does not exist.");
        }

        $authenticationScopes = $this->getUserConsentedAuthenticationScopesQuery->execute(
            $this->connectedPimUserProvider->getCurrentUserId(),
            $connectedApp->getId()
        );

        return new JsonResponse($authenticationScopes);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Mapper\ScopeToViewMessageMapper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetWizardDataAction
{
    private GetAppQueryInterface $getAppQuery;
    private AppAuthorizationSessionInterface $appAuthorizationSession;
    private ScopeToViewMessageMapper $scopeToViewMessageMapper;

    public function __construct(
        GetAppQueryInterface $getAppQuery,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        ScopeToViewMessageMapper $scopeToViewMessageMapper
    ) {
        $this->getAppQuery = $getAppQuery;
        $this->appAuthorizationSession = $appAuthorizationSession;
        $this->scopeToViewMessageMapper = $scopeToViewMessageMapper;
    }

    public function __invoke(Request $request, string $clientId): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $app = $this->getAppQuery->execute($clientId);
        if (null === $app) {
            throw new NotFoundHttpException("Invalid app identifier");
        }

        $appAuthorization = $this->appAuthorizationSession->getAppAuthorization($clientId);
        if (null === $appAuthorization) {
            throw new NotFoundHttpException("Invalid app identifier");
        }

        $normalizedApp = $app->normalize();
        $scopeMessages = $this->scopeToViewMessageMapper->getMessages($appAuthorization->scope);

        return new JsonResponse([
            'appName' => $normalizedApp['name'],
            'appLogo' => $normalizedApp['logo'],
            'scopeMessages' => $scopeMessages,
        ]);

    }
}

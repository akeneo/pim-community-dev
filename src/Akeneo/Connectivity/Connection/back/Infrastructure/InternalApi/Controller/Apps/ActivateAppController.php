<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller\Apps;

use Akeneo\Connectivity\Connection\Application\Marketplace\AppUrlGenerator;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ActivateAppController
{
    private GetAppQueryInterface  $getAppQuery;
    private ClientProviderInterface $clientProvider;
    private AppUrlGenerator $appUrlGenerator;

    public function __construct(
        GetAppQueryInterface $getAppQuery,
        ClientProviderInterface  $clientProvider,
        AppUrlGenerator $appUrlGenerator
    ) {
        $this->getAppQuery = $getAppQuery;
        $this->clientProvider = $clientProvider;
        $this->appUrlGenerator = $appUrlGenerator;
    }

    public function __invoke(Request $request, string $id): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $app = $this->getAppQuery->execute($id);
        if (null === $app) {
            throw new NotFoundHttpException("Invalid app identifier");
        }

        $app = $app->withPimUrlSource($this->appUrlGenerator->getAppQueryParameters());

        $this->clientProvider->findOrCreateClient($app);

        return new RedirectResponse($app->getActivateUrl());
    }
}

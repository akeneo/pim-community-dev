<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller;

use Akeneo\Connectivity\Connection\Application\Apps\Command\ConfirmAppAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConfirmAppAuthorizationHandler;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfirmAuthorizationAction
{
    private ConfirmAppAuthorizationHandler $handler;
    private FeatureFlag $featureFlag;

    public function __construct(
        ConfirmAppAuthorizationHandler $handler,
        FeatureFlag $featureFlag
    ) {
        $this->handler = $handler;
        $this->featureFlag = $featureFlag;
    }

    public function __invoke(Request $request, string $clientId): Response
    {
        if (!$this->featureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $this->handler->handle(new ConfirmAppAuthorizationCommand($clientId));

        return new JsonResponse(['clientId' => $clientId]);
    }
}

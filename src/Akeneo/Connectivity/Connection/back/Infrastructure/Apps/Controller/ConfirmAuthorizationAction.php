<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller;

use Akeneo\Connectivity\Connection\Application\Apps\Command\ConfirmAppAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConfirmAppAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequest;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Repository\AppRepositoryInterface;
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
    private AppRepositoryInterface $appRepository;

    public function __construct(
        ConfirmAppAuthorizationHandler $handler,
        FeatureFlag $featureFlag,
        AppRepositoryInterface $appRepository
    ) {
        $this->handler = $handler;
        $this->featureFlag = $featureFlag;
        $this->appRepository = $appRepository;
    }

    public function __invoke(Request $request, string $clientId): Response
    {
        if (!$this->featureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $app = $this->appRepository->findOneById($clientId);
        if(null === $app) {
            try {
                $app = $this->handler->handle(new ConfirmAppAuthorizationCommand($clientId));
            } catch (InvalidAppAuthorizationRequest $exception) {
                return new JsonResponse([
                    'error' => $exception->getConstraintViolationList()[0]->getMessage()
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        return new JsonResponse(['appId' => $app->getId()]);
    }
}

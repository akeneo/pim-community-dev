<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\InternalApi;

use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\AccessDeniedException;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Repository\ConnectedAppRepositoryInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionType;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateConnectedAppMonitoringSettingsAction
{
    public function __construct(
        private FeatureFlag $featureFlag,
        private SecurityFacade $security,
        private FindAConnectionHandler $findAConnectionHandler,
        private UpdateConnectionHandler $updateConnectionHandler,
        private ConnectedAppRepositoryInterface $connectedAppRepository,
    ) {
    }

    public function __invoke(Request $request, string $connectionCode): Response
    {
        if (!$this->featureFlag->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $connectedApp = $this->connectedAppRepository->findOneByConnectionCode($connectionCode);

        if (null === $connectedApp) {
            throw new NotFoundHttpException("Connected app with connection code $connectionCode does not exist.");
        }

        $connection = $this->findAConnectionHandler->handle(new FindAConnectionQuery($connectionCode));

        if (null === $connection || ConnectionType::APP_TYPE !== $connection->type()) {
            throw new NotFoundHttpException("Connection with connection code $connectionCode does not exist.");
        }

        $data = \json_decode($request->getContent(), true);
        $flowType = $data['flowType'] ?? $connection->flowType();
        $auditable = $data['auditable'] ?? $connection->auditable();

        if (false === \is_string($flowType) || false === \is_bool($auditable)) {
            return new JsonResponse(['error' => 'Wrong type for parameters'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $command = new UpdateConnectionCommand(
            $connection->code(),
            $connection->label(),
            $flowType,
            $connection->image(),
            $connection->userRoleId(),
            $connection->userGroupId(),
            $auditable
        );

        try {
            $this->updateConnectionHandler->handle($command);
        } catch (ConstraintViolationListException $e) {
            $errorList = $this->buildViolationResponse($e->getConstraintViolationList());

            return new JsonResponse(
                ['errors' => $errorList, 'message' => $e->getMessage()],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function buildViolationResponse(ConstraintViolationListInterface $constraintViolationList): array
    {
        $errors = [];
        foreach ($constraintViolationList as $constraintViolation) {
            $errors[] = [
                'name' => $constraintViolation->getPropertyPath(),
                'reason' => $constraintViolation->getMessage(),
            ];
        }

        return $errors;
    }

    private function denyAccessUnlessGrantedToManage(ConnectedApp $app): void
    {
        if (!$app->isTestApp() && !$this->security->isGranted('akeneo_connectivity_connection_manage_apps')) {
            throw new AccessDeniedException();
        }

        if ($app->isTestApp() && !$this->security->isGranted('akeneo_connectivity_connection_manage_test_apps')) {
            throw new AccessDeniedException();
        }
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Command\DeleteConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\DeleteConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionPasswordCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionPasswordHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionSecretCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionSecretHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FetchConnectionsHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Connection;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionController
{
    /** @var CreateConnectionHandler */
    private $createConnectionHandler;

    /** @var FetchConnectionsHandler */
    private $fetchConnectionsHandler;

    /** @var FindAConnectionHandler */
    private $findAConnectionHandler;

    /** @var UpdateConnectionHandler */
    private $updateConnectionHandler;

    /** @var DeleteConnectionHandler */
    private $deleteConnectionHandler;

    /** @var RegenerateConnectionSecretHandler */
    private $regenerateConnectionSecretHandler;

    private $regenerateConnectionPasswordHandler;

    /** @var SecurityFacade */
    private $securityFacade;

    public function __construct(
        CreateConnectionHandler $createConnectionHandler,
        FetchConnectionsHandler $fetchConnectionsHandler,
        FindAConnectionHandler $findAConnectionHandler,
        UpdateConnectionHandler $updateConnectionHandler,
        DeleteConnectionHandler $deleteConnectionHandler,
        RegenerateConnectionSecretHandler $regenerateConnectionSecretHandler,
        RegenerateConnectionPasswordHandler $regenerateConnectionPasswordHandler,
        SecurityFacade $securityFacade
    ) {
        $this->createConnectionHandler = $createConnectionHandler;
        $this->fetchConnectionsHandler = $fetchConnectionsHandler;
        $this->findAConnectionHandler = $findAConnectionHandler;
        $this->updateConnectionHandler = $updateConnectionHandler;
        $this->deleteConnectionHandler = $deleteConnectionHandler;
        $this->regenerateConnectionSecretHandler = $regenerateConnectionSecretHandler;
        $this->regenerateConnectionPasswordHandler = $regenerateConnectionPasswordHandler;
        $this->securityFacade = $securityFacade;
    }

    public function list(): JsonResponse
    {
        $connections = $this->fetchConnectionsHandler->query();

        return new JsonResponse(
            array_map(function (Connection $connection) {
                return $connection->normalize();
            }, $connections)
        );
    }

    public function create(Request $request): JsonResponse
    {
        if (true !== $this->securityFacade->isGranted('akeneo_connectivity_connection_manage_settings')) {
            throw new AccessDeniedException();
        }

        $data = json_decode($request->getContent(), true);
        // TODO: Valid JSON format

        $command = new CreateConnectionCommand($data['code'], $data['label'], $data['flow_type']);

        try {
            $connection = $this->createConnectionHandler->handle($command);
        } catch (ConstraintViolationListException $e) {
            $errorList = $this->buildViolationResponse($e->getConstraintViolationList());

            return new JsonResponse(
                ['errors' => $errorList, 'message' => $e->getMessage()],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($connection->normalize(), Response::HTTP_CREATED);
    }

    public function get(Request $request): JsonResponse
    {
        if (true !== $this->securityFacade->isGranted('akeneo_connectivity_connection_manage_settings')) {
            throw new AccessDeniedException();
        }

        $query = new FindAConnectionQuery($request->get('code', ''));
        $connection = $this->findAConnectionHandler->handle($query);

        if (null === $connection) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($connection->normalize());
    }

    public function update(Request $request): JsonResponse
    {
        if (true !== $this->securityFacade->isGranted('akeneo_connectivity_connection_manage_settings')) {
            throw new AccessDeniedException();
        }

        $data = json_decode($request->getContent(), true);
        // TODO: Valid JSON format

        $command = new UpdateConnectionCommand(
            $request->get('code', ''),
            $data['label'],
            $data['flow_type'],
            $data['image'],
            $data['user_role_id'],
            $data['user_group_id']
        );

        try {
            $this->updateConnectionHandler->handle($command);
        } catch (ConstraintViolationListException $e) {
            $errorList = $this->buildViolationResponse($e->getConstraintViolationList());

            return new JsonResponse(
                ['errors' => $errorList, 'message' => $e->getMessage()],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    public function delete(Request $request): JsonResponse
    {
        if (true !== $this->securityFacade->isGranted('akeneo_connectivity_connection_manage_settings')) {
            throw new AccessDeniedException();
        }

        $command = new DeleteConnectionCommand($request->get('code', ''));
        try {
            $this->deleteConnectionHandler->handle($command);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    public function regenerateSecret(Request $request): JsonResponse
    {
        if (true !== $this->securityFacade->isGranted('akeneo_connectivity_connection_manage_settings')) {
            throw new AccessDeniedException();
        }

        $command = new RegenerateConnectionSecretCommand($request->get('code', ''));
        try {
            $this->regenerateConnectionSecretHandler->handle($command);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    public function regeneratePassword(Request $request): JsonResponse
    {
        if (true !== $this->securityFacade->isGranted('akeneo_connectivity_connection_manage_settings')) {
            throw new AccessDeniedException();
        }

        $command = new RegenerateConnectionPasswordCommand($request->get('code', ''));
        try {
            $password = $this->regenerateConnectionPasswordHandler->handle($command);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['password' => $password]);
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
}

<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\InternalApi\Controller;

use Akeneo\Apps\Application\Command\CreateAppCommand;
use Akeneo\Apps\Application\Command\CreateAppHandler;
use Akeneo\Apps\Application\Command\DeleteAppCommand;
use Akeneo\Apps\Application\Command\DeleteAppHandler;
use Akeneo\Apps\Application\Command\RegenerateAppSecretCommand;
use Akeneo\Apps\Application\Command\RegenerateAppSecretHandler;
use Akeneo\Apps\Application\Command\UpdateAppCommand;
use Akeneo\Apps\Application\Command\UpdateAppHandler;
use Akeneo\Apps\Application\Query\FetchAppsHandler;
use Akeneo\Apps\Application\Query\FindAnAppHandler;
use Akeneo\Apps\Application\Query\FindAnAppQuery;
use Akeneo\Apps\Domain\Exception\ConstraintViolationListException;
use Akeneo\Apps\Domain\Model\Read\App;
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
class AppController
{
    /** @var CreateAppHandler */
    private $createAppHandler;

    /** @var FetchAppsHandler */
    private $fetchAppsHandler;

    /** @var FindAnAppHandler */
    private $findAnAppHandler;

    /** @var UpdateAppHandler */
    private $updateAppHandler;

    /** @var DeleteAppHandler */
    private $deleteAppHandler;

    /** @var RegenerateAppSecretHandler */
    private $regenerateAppSecretHandler;

    /** @var SecurityFacade */
    private $securityFacade;

    public function __construct(
        CreateAppHandler $createAppHandler,
        FetchAppsHandler $fetchAppsHandler,
        FindAnAppHandler $findAnAppHandler,
        UpdateAppHandler $updateAppHandler,
        DeleteAppHandler $deleteAppHandler,
        RegenerateAppSecretHandler $regenerateAppSecretHandler,
        SecurityFacade $securityFacade
    ) {
        $this->createAppHandler = $createAppHandler;
        $this->fetchAppsHandler = $fetchAppsHandler;
        $this->findAnAppHandler = $findAnAppHandler;
        $this->updateAppHandler = $updateAppHandler;
        $this->deleteAppHandler = $deleteAppHandler;
        $this->regenerateAppSecretHandler = $regenerateAppSecretHandler;
        $this->securityFacade = $securityFacade;
    }

    public function list(): JsonResponse
    {
        if (true !== $this->securityFacade->isGranted('akeneo_apps_manage_settings')) {
            throw new AccessDeniedException();
        }

        $apps = $this->fetchAppsHandler->query();

        return new JsonResponse(
            array_map(function (App $app) {
                return $app->normalize();
            }, $apps)
        );
    }

    public function create(Request $request): JsonResponse
    {
        if (true !== $this->securityFacade->isGranted('akeneo_apps_manage_settings')) {
            throw new AccessDeniedException();
        }

        $data = json_decode($request->getContent(), true);
        // TODO: Valid JSON format

        $command = new CreateAppCommand($data['code'], $data['label'], $data['flow_type']);

        try {
            $app = $this->createAppHandler->handle($command);
        } catch (ConstraintViolationListException $e) {
            $errorList = $this->buildViolationResponse($e->getConstraintViolationList());

            return new JsonResponse(
                ['errors' => $errorList, 'message' => $e->getMessage()],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($app->normalize());
    }

    public function get(Request $request): JsonResponse
    {
        if (true !== $this->securityFacade->isGranted('akeneo_apps_manage_settings')) {
            throw new AccessDeniedException();
        }

        $query = new FindAnAppQuery($request->get('code', ''));
        $app = $this->findAnAppHandler->handle($query);

        if (null === $app) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($app->normalize());
    }

    public function update(Request $request): JsonResponse
    {
        if (true !== $this->securityFacade->isGranted('akeneo_apps_manage_settings')) {
            throw new AccessDeniedException();
        }

        $data = json_decode($request->getContent(), true);
        // TODO: Valid JSON format

        $command = new UpdateAppCommand($request->get('code', ''), $data['label'], $data['flow_type']);

        try {
            $this->updateAppHandler->handle($command);
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
        if (true !== $this->securityFacade->isGranted('akeneo_apps_manage_settings')) {
            throw new AccessDeniedException();
        }

        $command = new DeleteAppCommand($request->get('code', ''));
        try {
            $this->deleteAppHandler->handle($command);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    public function regenerateSecret(Request $request): JsonResponse
    {
        if (true !== $this->securityFacade->isGranted('akeneo_apps_manage_settings')) {
            throw new AccessDeniedException();
        }

        $command = new RegenerateAppSecretCommand($request->get('code', ''));
        try {
            $this->regenerateAppSecretHandler->handle($command);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
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
}

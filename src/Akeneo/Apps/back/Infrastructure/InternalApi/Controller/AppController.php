<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\InternalApi\Controller;

use Akeneo\Apps\Application\Command\CreateAppCommand;
use Akeneo\Apps\Application\Command\CreateAppHandler;
use Akeneo\Apps\Application\Query\FetchAppsHandler;
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

    /** @var SecurityFacade */
    private $securityFacade;

    public function __construct(
        CreateAppHandler $createAppHandler,
        FetchAppsHandler $fetchAppsHandler,
        SecurityFacade $securityFacade
    ) {
        $this->createAppHandler = $createAppHandler;
        $this->fetchAppsHandler = $fetchAppsHandler;
        $this->securityFacade = $securityFacade;
    }

    public function list()
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

    public function create(Request $request)
    {
        if (true !== $this->securityFacade->isGranted('akeneo_apps_manage_settings')) {
            throw new AccessDeniedException();
        }

        $data = json_decode($request->getContent(), true);
        // TODO: Valid JSON format

        $command = new CreateAppCommand($data['code'], $data['label'], $data['flow_type']);

        try {
            $this->createAppHandler->handle($command);
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

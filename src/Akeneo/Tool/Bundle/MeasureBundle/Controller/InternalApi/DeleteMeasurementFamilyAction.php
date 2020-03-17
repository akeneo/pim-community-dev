<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Controller\InternalApi;

use Akeneo\Tool\Bundle\MeasureBundle\Application\DeleteMeasurementFamily\DeleteMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Application\DeleteMeasurementFamily\DeleteMeasurementFamilyHandler;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteMeasurementFamilyAction
{
    /** @var ValidatorInterface */
    private $validator;

    /** @var DeleteMeasurementFamilyHandler */
    private $deleteMeasurementFamilyHandler;

    public function __construct(
        ValidatorInterface $validator,
        DeleteMeasurementFamilyHandler $deleteMeasurementFamilyHandler
    ) {
        $this->validator = $validator;
        $this->deleteMeasurementFamilyHandler = $deleteMeasurementFamilyHandler;
    }

    public function __invoke(Request $request, string $code): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        try {
            $deleteMeasurementFamilyCommand = $this->createDeleteMeasurementFamilyCommand($code);
            $this->validateDeleteMeasurementFamilyCommand($deleteMeasurementFamilyCommand);
            $this->handleDeleteMeasurementFamilyCommand($deleteMeasurementFamilyCommand);
        } catch (MeasurementFamilyNotFoundException $ex) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        } catch (ViolationHttpException $ex) {
            return new JsonResponse(
                [
                    'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => 'The measurement family cannot be removed.',
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    private function createDeleteMeasurementFamilyCommand(string $code)
    {
        $deleteMeasurementFamilyCommand = new DeleteMeasurementFamilyCommand();
        $deleteMeasurementFamilyCommand->code = $code;

        return $deleteMeasurementFamilyCommand;
    }

    private function validateDeleteMeasurementFamilyCommand(
        DeleteMeasurementFamilyCommand $deleteMeasurementFamilyCommand
    ) {
        $violations = $this->validator->validate($deleteMeasurementFamilyCommand);

        if (count($violations) > 0) {
            throw new ViolationHttpException($violations);
        }
    }

    /**
     * @throws MeasurementFamilyNotFoundException
     */
    private function handleDeleteMeasurementFamilyCommand(
        DeleteMeasurementFamilyCommand $deleteMeasurementFamilyCommand
    ) {
        $this->deleteMeasurementFamilyHandler->handle($deleteMeasurementFamilyCommand);
    }
}

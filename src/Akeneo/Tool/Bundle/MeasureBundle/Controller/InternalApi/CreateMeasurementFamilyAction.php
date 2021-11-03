<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Controller\InternalApi;

use Akeneo\Tool\Bundle\MeasureBundle\Application\CreateMeasurementFamily\CreateMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Application\CreateMeasurementFamily\CreateMeasurementFamilyHandler;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateMeasurementFamilyAction
{
    private ValidatorInterface $validator;

    private NormalizerInterface $violationNormalizer;

    private CreateMeasurementFamilyHandler $createMeasurementFamilyHandler;

    private SecurityFacade $securityFacade;

    public function __construct(
        ValidatorInterface $validator,
        NormalizerInterface $violationNormalizer,
        CreateMeasurementFamilyHandler $createMeasurementFamilyHandler,
        SecurityFacade $securityFacade
    ) {
        $this->validator                      = $validator;
        $this->violationNormalizer            = $violationNormalizer;
        $this->createMeasurementFamilyHandler = $createMeasurementFamilyHandler;
        $this->securityFacade                 = $securityFacade;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->securityFacade->isGranted('akeneo_measurements_manage_settings') ||
            !$this->securityFacade->isGranted('akeneo_measurements_measurement_family_create')
        ) {
            throw new AccessDeniedException();
        }

        $decodedRequest = $this->decodeRequest($request);
        $createMeasurementFamilyCommand = $this->createCreateMeasurementFamilyCommand($decodedRequest);

        $violations = $this->validator->validate($createMeasurementFamilyCommand);
        if ($violations->count() > 0) {
            return new JsonResponse($this->violationNormalizer->normalize($violations), Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->handleCreateMeasurementFamilyCommand($createMeasurementFamilyCommand);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(
                [
                    'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => $exception->getMessage(),
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return new Response(null, Response::HTTP_CREATED);
    }

    private function decodeRequest(Request $request): array
    {
        $normalizedRequest = json_decode($request->getContent(), true);

        if (null === $normalizedRequest) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $normalizedRequest;
    }

    private function createCreateMeasurementFamilyCommand(
        array $normalizedMeasurementFamily
    ): CreateMeasurementFamilyCommand {
        $createMeasurementFamilyCommand = new CreateMeasurementFamilyCommand();
        $createMeasurementFamilyCommand->code = $normalizedMeasurementFamily['code'];
        $createMeasurementFamilyCommand->standardUnitCode = $normalizedMeasurementFamily['standard_unit_code'];
        $createMeasurementFamilyCommand->labels = $normalizedMeasurementFamily['labels'];
        $createMeasurementFamilyCommand->units = $normalizedMeasurementFamily['units'];

        return $createMeasurementFamilyCommand;
    }

    private function handleCreateMeasurementFamilyCommand(CreateMeasurementFamilyCommand $createMeasurementFamilyCommand)
    {
        $this->createMeasurementFamilyHandler->handle($createMeasurementFamilyCommand);
    }
}

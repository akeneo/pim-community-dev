<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Controller\InternalApi;

use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyHandler;
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
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveMeasurementFamilyAction
{
    private ValidatorInterface $validator;

    private NormalizerInterface $violationNormalizer;

    private SaveMeasurementFamilyHandler $saveMeasurementFamilyHandler;

    private SecurityFacade $securityFacade;

    public function __construct(
        ValidatorInterface $validator,
        SaveMeasurementFamilyHandler $saveMeasurementFamilyHandler,
        NormalizerInterface $violationNormalizer,
        SecurityFacade $securityFacade
    ) {
        $this->validator                    = $validator;
        $this->saveMeasurementFamilyHandler = $saveMeasurementFamilyHandler;
        $this->violationNormalizer          = $violationNormalizer;
        $this->securityFacade               = $securityFacade;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if ($this->hasDesynchronizedCode($request)) {
            return new JsonResponse(
                'The identifier provided in the route and the one given in the body of the request are different',
                Response::HTTP_BAD_REQUEST
            );
        }
        if (!(
            $this->securityFacade->isGranted('akeneo_measurements_manage_settings') &&
                (
                    $this->securityFacade->isGranted('akeneo_measurements_measurement_family_edit_properties') ||
                    $this->securityFacade->isGranted('akeneo_measurements_measurement_family_delete') ||
                    $this->securityFacade->isGranted('akeneo_measurements_measurement_unit_add') ||
                    $this->securityFacade->isGranted('akeneo_measurements_measurement_unit_edit') ||
                    $this->securityFacade->isGranted('akeneo_measurements_measurement_unit_delete')
                )
        )
        ) {
            throw new AccessDeniedException();
        }

        $decodedRequest = $this->decodeRequest($request);
        $saveMeasurementFamilyCommand = $this->createSaveMeasurementFamilyCommand($decodedRequest);

        $violations = $this->validator->validate($saveMeasurementFamilyCommand);
        if ($violations->count() > 0) {
            return new JsonResponse($this->violationNormalizer->normalize($violations), Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->saveMeasurementFamilyHandler->handle($saveMeasurementFamilyCommand);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(
                ['code' => Response::HTTP_UNPROCESSABLE_ENTITY, 'message' => $exception->getMessage()],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return new Response(null, Response::HTTP_CREATED);
    }

    private function createSaveMeasurementFamilyCommand(
        array $normalizedMeasurementFamily
    ): SaveMeasurementFamilyCommand {
        $saveMeasurementFamilyCommand = new SaveMeasurementFamilyCommand();
        $saveMeasurementFamilyCommand->code = $normalizedMeasurementFamily['code'];
        $saveMeasurementFamilyCommand->standardUnitCode = $normalizedMeasurementFamily['standard_unit_code'];
        $saveMeasurementFamilyCommand->labels = $normalizedMeasurementFamily['labels'];
        $saveMeasurementFamilyCommand->units = $normalizedMeasurementFamily['units'];

        return $saveMeasurementFamilyCommand;
    }

    private function decodeRequest(Request $request): array
    {
        $normalizedRequest = json_decode($request->getContent(), true);

        if (null === $normalizedRequest) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $normalizedRequest;
    }

    /**
     * Checks whether the identifier given in the url parameter and in the body are the same or not.
     */
    private function hasDesynchronizedCode(Request $request): bool
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        return $normalizedCommand['code'] !== $request->get('measurement_family_code');
    }
}

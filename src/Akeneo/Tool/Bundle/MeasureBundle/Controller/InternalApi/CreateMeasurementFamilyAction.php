<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Controller\InternalApi;

use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyHandler;
use Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchemaErrorsFormatter;
use Akeneo\Tool\Bundle\MeasureBundle\Controller\InternalApi\JsonSchema\MeasurementFamilyStructureValidator;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\Api\Normalizer\Exception\ViolationNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateMeasurementFamilyAction
{
    /** @var MeasurementFamilyStructureValidator */
    private $measurementFamilyStructureValidator;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ViolationNormalizer */
    private $violationNormalizer;

    /** @var SaveMeasurementFamilyHandler */
    private $saveMeasurementFamilyHandler;

    public function __construct(
        MeasurementFamilyStructureValidator $measurementFamilyStructureValidator,
        ValidatorInterface $validator,
        ViolationNormalizer $violationNormalizer,
        SaveMeasurementFamilyHandler $saveMeasurementFamilyHandler
    ) {
        $this->measurementFamilyStructureValidator = $measurementFamilyStructureValidator;
        $this->validator = $validator;
        $this->violationNormalizer = $violationNormalizer;
        $this->saveMeasurementFamilyHandler = $saveMeasurementFamilyHandler;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $decodedRequest = $this->decodeRequest($request);
        $structureErrors = $this->validateDecodedRequest($decodedRequest);

        if (!empty($structureErrors)) {
            return new JsonResponse(
                [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'The measurement family has an invalid format.',
                    'errors' => JsonSchemaErrorsFormatter::format($structureErrors),
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $saveMeasurementFamilyCommand = $this->createSaveMeasurementFamilyCommand($decodedRequest);

        try {
            $this->validateSaveMeasurementFamilyCommand($saveMeasurementFamilyCommand);
            $this->handleSaveMeasurementFamilyCommand($saveMeasurementFamilyCommand);
        } catch (\InvalidArgumentException $ex) {
            return new JsonResponse(
                [
                    'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => $ex->getMessage(),
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (ViolationHttpException $ex) {
            return new JsonResponse(
                $this->violationNormalizer->normalize($ex),
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

    private function validateDecodedRequest(array $decodedRequest): array
    {
        return $this->measurementFamilyStructureValidator->validate($decodedRequest);
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

    private function validateSaveMeasurementFamilyCommand(SaveMeasurementFamilyCommand $saveMeasurementFamilyCommand)
    {
        $violations = $this->validator->validate($saveMeasurementFamilyCommand);

        if (count($violations) > 0) {
            throw new ViolationHttpException(
                $violations,
                'The measurement family has data that does not comply with the business rules.'
            );
        }
    }

    private function handleSaveMeasurementFamilyCommand(SaveMeasurementFamilyCommand $saveMeasurementFamilyCommand)
    {
        $this->saveMeasurementFamilyHandler->handle($saveMeasurementFamilyCommand);
    }
}

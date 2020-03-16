<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Controller\InternalApi;

use Akeneo\Tool\Bundle\MeasureBundle\Application\CreateMeasurementFamily\CreateMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Application\CreateMeasurementFamily\CreateMeasurementFamilyHandler;
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

    /** @var CreateMeasurementFamilyHandler */
    private $createMeasurementFamilyHandler;

    public function __construct(
        MeasurementFamilyStructureValidator $measurementFamilyStructureValidator,
        ValidatorInterface $validator,
        ViolationNormalizer $violationNormalizer,
        CreateMeasurementFamilyHandler $createMeasurementFamilyHandler
    ) {
        $this->measurementFamilyStructureValidator = $measurementFamilyStructureValidator;
        $this->validator = $validator;
        $this->violationNormalizer = $violationNormalizer;
        $this->createMeasurementFamilyHandler = $createMeasurementFamilyHandler;
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

        $createMeasurementFamilyCommand = $this->createCreateMeasurementFamilyCommand($decodedRequest);

        try {
            $this->validateCreateMeasurementFamilyCommand($createMeasurementFamilyCommand);
            $this->handleCreateMeasurementFamilyCommand($createMeasurementFamilyCommand);
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

    private function validateCreateMeasurementFamilyCommand(CreateMeasurementFamilyCommand $createMeasurementFamilyCommand)
    {
        $violations = $this->validator->validate($createMeasurementFamilyCommand);

        if (count($violations) > 0) {
            throw new ViolationHttpException(
                $violations,
                'The measurement family has data that does not comply with the business rules.'
            );
        }
    }

    private function handleCreateMeasurementFamilyCommand(CreateMeasurementFamilyCommand $createMeasurementFamilyCommand)
    {
        $this->createMeasurementFamilyHandler->handle($createMeasurementFamilyCommand);
    }
}

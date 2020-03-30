<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi;

use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyHandler;
use Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchema\MeasurementFamilyListValidator;
use Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchema\MeasurementFamilyValidator;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\Api\Normalizer\Exception\ViolationNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveMeasurementFamiliesAction
{
    /** * @var MeasurementFamilyListValidator */
    private $measurementFamilyListValidator;

    /** @var MeasurementFamilyValidator */
    private $measurementFamilyStructureValidator;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ViolationNormalizer */
    private $violationNormalizer;

    /** @var SaveMeasurementFamilyHandler */
    private $saveMeasurementFamilyHandler;

    public function __construct(
        MeasurementFamilyListValidator $measurementFamilyListValidator,
        MeasurementFamilyValidator $measurementFamilyStructureValidator,
        ValidatorInterface $validator,
        ViolationNormalizer $violationNormalizer,
        SaveMeasurementFamilyHandler $saveMeasurementFamilyHandler
    ) {
        $this->measurementFamilyListValidator = $measurementFamilyListValidator;
        $this->measurementFamilyStructureValidator = $measurementFamilyStructureValidator;
        $this->validator = $validator;
        $this->violationNormalizer = $violationNormalizer;
        $this->saveMeasurementFamilyHandler = $saveMeasurementFamilyHandler;
    }

    public function __invoke(Request $request): Response
    {
        $normalizedMeasurementFamilies = $this->getNormalizedMeasurementFamiliesFromRequest($request);
        $structureErrors = $this->measurementFamilyListValidator->validate($normalizedMeasurementFamilies);

        if (!empty($structureErrors)) {
            return new JsonResponse(
                [
                    'code'    => Response::HTTP_BAD_REQUEST,
                    'message' => 'The list of measurement families has an invalid format.',
                    'errors'  => JsonSchemaErrorsFormatter::format($structureErrors),
                ], Response::HTTP_BAD_REQUEST
            );
        }

        $responsesData = [];
        foreach ($normalizedMeasurementFamilies as $normalizedMeasurementFamily) {
            try {
                $responseData = $this->createOrUpdateMeasurementFamily($normalizedMeasurementFamily);
            } catch (\InvalidArgumentException $exception) {
                $responseData = [
                    'code'        => $normalizedMeasurementFamily['code'],
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message'     => $exception->getMessage()
                ];
            } catch (ViolationHttpException $exception) {
                $responseData = [
                    'code'        => $normalizedMeasurementFamily['code'],
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY
                ];
                $responseData += $this->violationNormalizer->normalize($exception);
            }

            $responsesData[] = $responseData;
        }

        return new JsonResponse($responsesData);
    }

    private function getNormalizedMeasurementFamiliesFromRequest(Request $request): array
    {
        $normalizedMeasurementFamilies = json_decode($request->getContent(), true);

        if (null === $normalizedMeasurementFamilies) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $normalizedMeasurementFamilies;
    }

    private function createOrUpdateMeasurementFamily(array $normalizedMeasurementFamily)
    {
        $structureErrors = $this->measurementFamilyStructureValidator->validate($normalizedMeasurementFamily);
        if (!empty($structureErrors)) {
            return [
                'code'        => $normalizedMeasurementFamily['code'] ?? '',
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message'     => 'The measurement family has an invalid format.',
                'errors'      => JsonSchemaErrorsFormatter::format($structureErrors),
            ];
        }

        $saveMeasurementFamilyCommand = $this->saveMeasurementFamilyCommand($normalizedMeasurementFamily);
        $violations = $this->validator->validate($saveMeasurementFamilyCommand);
        if ($violations->count() > 0) {
            throw new ViolationHttpException(
                $violations,
                'The measurement family has data that does not comply with the business rules.'
            );
        }

        $this->saveMeasurementFamilyHandler->handle($saveMeasurementFamilyCommand);

        return [
            'code'        => $saveMeasurementFamilyCommand->code,
            'status_code' => Response::HTTP_NO_CONTENT, // TODO: Should it return 201 in case it's created ?
        ];
    }

    private function saveMeasurementFamilyCommand(array $normalizedMeasurementFamily): SaveMeasurementFamilyCommand
    {
        $saveMeasurementFamilyCommand = new SaveMeasurementFamilyCommand;
        $saveMeasurementFamilyCommand->code = $normalizedMeasurementFamily['code'];
        $saveMeasurementFamilyCommand->standardUnitCode = $normalizedMeasurementFamily['standard_unit_code'];
        $saveMeasurementFamilyCommand->labels = $normalizedMeasurementFamily['labels'];
        $saveMeasurementFamilyCommand->units = $normalizedMeasurementFamily['units'];

        return $saveMeasurementFamilyCommand;
    }
}

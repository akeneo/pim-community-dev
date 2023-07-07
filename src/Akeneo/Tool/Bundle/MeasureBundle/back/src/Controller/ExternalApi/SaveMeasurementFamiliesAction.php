<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi;

use Akeneo\Tool\Bundle\MeasureBundle\Application\CreateMeasurementFamily\CreateMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Application\CreateMeasurementFamily\CreateMeasurementFamilyHandler;
use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyHandler;
use Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchema\MeasurementFamilyCommonStructureValidator;
use Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchema\MeasurementFamilyListValidator;
use Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchema\MeasurementFamilyValidator;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily\UnitsMustBeIndexedByCode;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\Api\Normalizer\Exception\ViolationNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveMeasurementFamiliesAction
{
    private MeasurementFamilyListValidator $measurementFamilyListValidator;

    private MeasurementFamilyCommonStructureValidator $measurementFamilyCommonStructureValidator;

    private MeasurementFamilyValidator $measurementFamilyStructureValidator;

    private ValidatorInterface $validator;

    private ViolationNormalizer $violationNormalizer;

    private SaveMeasurementFamilyHandler $saveMeasurementFamilyHandler;

    private CreateMeasurementFamilyHandler $createMeasurementFamilyHandler;

    private MeasurementFamilyRepositoryInterface $measurementFamilyRepository;

    public function __construct(
        MeasurementFamilyListValidator $measurementFamilyListValidator,
        MeasurementFamilyCommonStructureValidator $measurementFamilyCommonStructureValidator,
        MeasurementFamilyValidator $measurementFamilyStructureValidator,
        ValidatorInterface $validator,
        ViolationNormalizer $violationNormalizer,
        SaveMeasurementFamilyHandler $saveMeasurementFamilyHandler,
        CreateMeasurementFamilyHandler $createMeasurementFamilyHandler,
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository
    ) {
        $this->measurementFamilyListValidator = $measurementFamilyListValidator;
        $this->measurementFamilyCommonStructureValidator = $measurementFamilyCommonStructureValidator;
        $this->measurementFamilyStructureValidator = $measurementFamilyStructureValidator;
        $this->validator = $validator;
        $this->violationNormalizer = $violationNormalizer;
        $this->saveMeasurementFamilyHandler = $saveMeasurementFamilyHandler;
        $this->createMeasurementFamilyHandler = $createMeasurementFamilyHandler;
        $this->measurementFamilyRepository = $measurementFamilyRepository;
    }

    public function __invoke(Request $request): Response
    {
        $normalizedMeasurementFamilies = $this->getNormalizedMeasurementFamiliesFromRequest($request);
        $structureErrors = $this->measurementFamilyListValidator->validate($normalizedMeasurementFamilies);

        if ($structureErrors !== []) {
            return new JsonResponse([
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => 'The list of measurement families has an invalid format.',
                'errors' => JsonSchemaErrorsFormatter::format($structureErrors),
            ], Response::HTTP_BAD_REQUEST);
        }

        $responses = [];

        foreach ($normalizedMeasurementFamilies as $normalizedMeasurementFamily) {
            $structureErrors = $this->measurementFamilyCommonStructureValidator->validate($normalizedMeasurementFamily);

            if ($structureErrors !== []) {
                $responses[] = [
                    'code' => $normalizedMeasurementFamily['code'] ?? '',
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => 'The measurement family has an invalid format.',
                    'errors' => JsonSchemaErrorsFormatter::format($structureErrors),
                ];
                continue;
            }

            try {
                $measurementFamilyCode = MeasurementFamilyCode::fromString($normalizedMeasurementFamily['code']);
                $measurementFamily = $this->findMeasurementFamily($measurementFamilyCode);

                if (!$measurementFamily instanceof MeasurementFamily) {
                    $responses[] = $this->createMeasurementFamily($normalizedMeasurementFamily);
                } else {
                    $responses[] = $this->updateMeasurementFamily($normalizedMeasurementFamily, $measurementFamily);
                }
            } catch (\InvalidArgumentException $exception) {
                $responses[] = [
                    'code' => $normalizedMeasurementFamily['code'],
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => $exception->getMessage()
                ];
            } catch (ViolationHttpException $exception) {
                $response = [
                    'code' => $normalizedMeasurementFamily['code'],
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY
                ];
                $response += $this->violationNormalizer->normalize($exception);
                $responses[] = $response;
            }
        }

        return new JsonResponse($responses);
    }

    private function findMeasurementFamily(MeasurementFamilyCode $measurementFamilyCode): ?MeasurementFamily
    {
        try {
            return $this->measurementFamilyRepository->getByCode($measurementFamilyCode);
        } catch (MeasurementFamilyNotFoundException $exception) {
            return null;
        }
    }

    private function createMeasurementFamily(array $normalizedMeasurementFamily): array
    {
        $structureErrors = $this->measurementFamilyStructureValidator->validate($normalizedMeasurementFamily);
        if ($structureErrors !== []) {
            return [
                'code' => $normalizedMeasurementFamily['code'] ?? '',
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'The measurement family has an invalid format.',
                'errors' => JsonSchemaErrorsFormatter::format($structureErrors),
            ];
        }

        $this->assertUnitsAreCorrectlyIndexed($normalizedMeasurementFamily);

        $createMeasurementFamilyCommand = $this->createMeasurementFamilyCommand($normalizedMeasurementFamily);
        $this->assertCreateMeasurementFamilyCommandIsValid($createMeasurementFamilyCommand);

        $this->createMeasurementFamilyHandler->handle($createMeasurementFamilyCommand);

        return [
            'code' => $createMeasurementFamilyCommand->code,
            'status_code' => Response::HTTP_CREATED,
        ];
    }

    /**
     * @throws ViolationHttpException
     */
    private function assertCreateMeasurementFamilyCommandIsValid(
        CreateMeasurementFamilyCommand $createMeasurementFamilyCommand
    ) {
        $violations = $this->validator->validate($createMeasurementFamilyCommand);
        if ($violations->count() > 0) {
            throw new ViolationHttpException(
                $violations,
                'The measurement family has data that does not comply with the business rules.'
            );
        }
    }

    private function updateMeasurementFamily(
        array $normalizedMeasurementFamily,
        MeasurementFamily $measurementFamily
    ): array {
        $normalizedMeasurementFamily = array_replace_recursive(
            $measurementFamily->normalizeWithIndexedUnits(),
            $normalizedMeasurementFamily
        );

        $structureErrors = $this->measurementFamilyStructureValidator->validate($normalizedMeasurementFamily);
        if ($structureErrors !== []) {
            return [
                'code' => $normalizedMeasurementFamily['code'] ?? '',
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'The measurement family has an invalid format.',
                'errors' => JsonSchemaErrorsFormatter::format($structureErrors),
            ];
        }

        $this->assertUnitsAreCorrectlyIndexed($normalizedMeasurementFamily);

        $saveMeasurementFamilyCommand = $this->saveMeasurementFamilyCommand($normalizedMeasurementFamily);
        $this->assertSaveMeasurementFamilyCommandIsValid($saveMeasurementFamilyCommand);

        $this->saveMeasurementFamilyHandler->handle($saveMeasurementFamilyCommand);

        return [
            'code' => $saveMeasurementFamilyCommand->code,
            'status_code' => Response::HTTP_NO_CONTENT,
        ];
    }

    /**
     * @throws ViolationHttpException
     */
    private function assertSaveMeasurementFamilyCommandIsValid(
        SaveMeasurementFamilyCommand $saveMeasurementFamilyCommand
    ) {
        $violations = $this->validator->validate($saveMeasurementFamilyCommand);
        if ($violations->count() > 0) {
            throw new ViolationHttpException(
                $violations,
                'The measurement family has data that does not comply with the business rules.'
            );
        }
    }

    /**
     * @throws ViolationHttpException
     */
    private function assertUnitsAreCorrectlyIndexed(array $normalizedMeasurementFamily)
    {
        $violations = $this->validator->validate($normalizedMeasurementFamily, [
            new Assert\Collection([
                'fields' => [
                    'units' => new UnitsMustBeIndexedByCode(),
                ],
                'allowExtraFields' => true,
            ]),
        ]);
        if ($violations->count() > 0) {
            throw new ViolationHttpException(
                $violations,
                'The measurement family has data that does not comply with the business rules.'
            );
        }
    }

    private function getNormalizedMeasurementFamiliesFromRequest(Request $request): array
    {
        $normalizedMeasurementFamilies = json_decode($request->getContent(), true);

        if (null === $normalizedMeasurementFamilies) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $normalizedMeasurementFamilies;
    }

    private function saveMeasurementFamilyCommand(array $normalizedMeasurementFamily): SaveMeasurementFamilyCommand
    {
        $saveMeasurementFamilyCommand = new SaveMeasurementFamilyCommand;
        $saveMeasurementFamilyCommand->code = $normalizedMeasurementFamily['code'];
        $saveMeasurementFamilyCommand->standardUnitCode = $normalizedMeasurementFamily['standard_unit_code'];
        $saveMeasurementFamilyCommand->labels = $normalizedMeasurementFamily['labels'] ?? [];
        $saveMeasurementFamilyCommand->units = $this->getNormalizedUnitsFromNormalizedMeasurementFamily($normalizedMeasurementFamily);

        return $saveMeasurementFamilyCommand;
    }

    private function createMeasurementFamilyCommand(array $normalizedMeasurementFamily): CreateMeasurementFamilyCommand
    {
        $createMeasurementFamilyCommand = new CreateMeasurementFamilyCommand();
        $createMeasurementFamilyCommand->code = $normalizedMeasurementFamily['code'];
        $createMeasurementFamilyCommand->standardUnitCode = $normalizedMeasurementFamily['standard_unit_code'];
        $createMeasurementFamilyCommand->labels = $normalizedMeasurementFamily['labels'] ?? [];
        $createMeasurementFamilyCommand->units = $this->getNormalizedUnitsFromNormalizedMeasurementFamily($normalizedMeasurementFamily);

        return $createMeasurementFamilyCommand;
    }

    /**
     * This method ensures that the units will have all the properties, even the optional ones.
     */
    private function getNormalizedUnitsFromNormalizedMeasurementFamily(array $normalizedMeasurementFamily): array
    {
        return array_map(static fn (array $unit) => [
          'code' => $unit['code'],
          'convert_from_standard' => $unit['convert_from_standard'],
          'labels' => $unit['labels'] ?? [],
          'symbol' => $unit['symbol'] ?? '',
        ], array_values($normalizedMeasurementFamily['units'] ?? []));
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record;

use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordHandler;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\Connector\EditRecordCommandFactory;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\EditRecordHandler;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordExistsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\JsonSchemaErrorsFormatter;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\RecordListValidator;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\RecordValidator;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\Api\Normalizer\Exception\ViolationNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateOrUpdateRecordsAction
{
    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    /** @var RecordExistsInterface */
    private $recordExists;

    /** @var EditRecordCommandFactory */
    private $editRecordCommandFactory;

    /** @var EditRecordHandler */
    private $editRecordHandler;

    /** @var CreateRecordHandler */
    private $createRecordHandler;

    /** @var Router */
    private $router;

    /** @var ValidatorInterface */
    private $recordDataValidator;

    /** @var ViolationNormalizer */
    private $violationNormalizer;

    /** @var RecordValidator */
    private $recordStructureValidator;

    /** @var RecordListValidator */
    private $recordListValidator;

    /** @var int */
    private $maximumRecordsPerRequest;

    public function __construct(
        ReferenceEntityExistsInterface $referenceEntityExists,
        RecordExistsInterface $recordExists,
        EditRecordCommandFactory $editRecordCommandFactory,
        EditRecordHandler $editRecordHandler,
        CreateRecordHandler $createRecordHandler,
        Router $router,
        ValidatorInterface $recordDataValidator,
        ViolationNormalizer $violationNormalizer,
        RecordValidator $recordStructureValidator,
        RecordListValidator $recordListValidator,
        int $maximumRecordsPerRequest
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->recordExists = $recordExists;
        $this->editRecordCommandFactory = $editRecordCommandFactory;
        $this->editRecordHandler = $editRecordHandler;
        $this->createRecordHandler = $createRecordHandler;
        $this->router = $router;
        $this->recordDataValidator = $recordDataValidator;
        $this->violationNormalizer = $violationNormalizer;
        $this->recordStructureValidator = $recordStructureValidator;
        $this->recordListValidator = $recordListValidator;
        $this->maximumRecordsPerRequest = $maximumRecordsPerRequest;
    }

    public function __invoke(Request $request, string $referenceEntityIdentifier): Response
    {
        try {
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        if (!$this->referenceEntityExists->withIdentifier($referenceEntityIdentifier)) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier));
        }

        $normalizedRecords = $this->getNormalizedRecordsFromRequest($request);
        $structureErrors = $this->recordListValidator->validate($normalizedRecords);

        if (!empty($structureErrors)) {
            return new JsonResponse([
                'code'    => Response::HTTP_BAD_REQUEST,
                'message' => 'The list of records has an invalid format.',
                'errors'  => JsonSchemaErrorsFormatter::format($structureErrors),
            ], Response::HTTP_BAD_REQUEST);
        }

        if (count($normalizedRecords) > $this->maximumRecordsPerRequest) {
            return new JsonResponse([
                'code'    => Response::HTTP_REQUEST_ENTITY_TOO_LARGE,
                'message' => sprintf('Too many resources to process, %d is the maximum allowed.', $this->maximumRecordsPerRequest),
            ], Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
        }

        $responsesData = [];
        foreach ($normalizedRecords as $normalizedRecord) {
            try {
                $responseData = $this->createOrUpdateRecord($referenceEntityIdentifier, $normalizedRecord);
            } catch (\InvalidArgumentException $exception) {
                $responseData = [
                    'code'        => $normalizedRecord['code'],
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message'     => $exception->getMessage()
                ];
            } catch (ViolationHttpException $exception) {
                $responseData = [
                    'code'        => $normalizedRecord['code'],
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY
                ];
                $responseData += $this->violationNormalizer->normalize($exception);
            }

            $responsesData[] = $responseData;
        }

        return new JsonResponse($responsesData);
    }

    private function getNormalizedRecordsFromRequest(Request $request): array
    {
        $normalizedRecords = json_decode($request->getContent(), true);

        if (null === $normalizedRecords) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $normalizedRecords;
    }

    private function createOrUpdateRecord(ReferenceEntityIdentifier $referenceEntityIdentifier, array $normalizedRecord): array
    {
        $structureErrors = $this->recordStructureValidator->validate($referenceEntityIdentifier, $normalizedRecord);

        if (!empty($structureErrors)) {
            return [
                'code'        => $normalizedRecord['code'] ?? '',
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message'     => 'The record has an invalid format.',
                'errors'      => JsonSchemaErrorsFormatter::format($structureErrors),
            ];
        }

        $recordCode = RecordCode::fromString($normalizedRecord['code']);
        $shouldBeCreated = !$this->recordExists->withReferenceEntityAndCode($referenceEntityIdentifier, $recordCode);
        $createRecordCommand = null;

        if (true === $shouldBeCreated) {
            $createRecordCommand = new CreateRecordCommand(
                $referenceEntityIdentifier->normalize(),
                $normalizedRecord['code'],
                []
            );

            $violations = $this->recordDataValidator->validate($createRecordCommand);
            if ($violations->count() > 0) {
                throw new ViolationHttpException($violations, 'The record has data that does not comply with the business rules.');
            }
        }

        $editRecordCommand = $this->editRecordCommandFactory->create($referenceEntityIdentifier, $normalizedRecord);

        $violations = $this->recordDataValidator->validate($editRecordCommand);
        if ($violations->count() > 0) {
            throw new ViolationHttpException($violations, 'The record has data that does not comply with the business rules.');
        }

        if (true === $shouldBeCreated) {
            ($this->createRecordHandler)($createRecordCommand);
        }

        ($this->editRecordHandler)($editRecordCommand);

        return [
            'code' => (string) $recordCode,
            'status_code' => $shouldBeCreated ? Response::HTTP_CREATED : Response::HTTP_NO_CONTENT,
        ];
    }
}

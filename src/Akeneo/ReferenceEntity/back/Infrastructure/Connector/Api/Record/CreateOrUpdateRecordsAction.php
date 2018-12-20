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
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Router;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
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

    /** @var NormalizerInterface */
    private $violationNormalizer;

    public function __construct(
        ReferenceEntityExistsInterface $referenceEntityExists,
        RecordExistsInterface $recordExists,
        EditRecordCommandFactory $editRecordCommandFactory,
        EditRecordHandler $editRecordHandler,
        CreateRecordHandler $createRecordHandler,
        Router $router,
        ValidatorInterface $recordDataValidator,
        NormalizerInterface $violationNormalizer
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->recordExists = $recordExists;
        $this->editRecordCommandFactory = $editRecordCommandFactory;
        $this->editRecordHandler = $editRecordHandler;
        $this->createRecordHandler = $createRecordHandler;
        $this->router = $router;
        $this->recordDataValidator = $recordDataValidator;
        $this->violationNormalizer = $violationNormalizer;
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
        $recordCode = RecordCode::fromString($normalizedRecord['code']);
        $shouldBeCreated = !$this->recordExists->withReferenceEntityAndCode($referenceEntityIdentifier, $recordCode);
        $createRecordCommand = null;

        if (true === $shouldBeCreated) {
            $createRecordCommand = new CreateRecordCommand();
            $createRecordCommand->code = $normalizedRecord['code'];
            $createRecordCommand->referenceEntityIdentifier = $referenceEntityIdentifier->normalize();
            $createRecordCommand->labels = [];

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

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
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordCommand;
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

    public function __construct(
        ReferenceEntityExistsInterface $referenceEntityExists,
        RecordExistsInterface $recordExists,
        EditRecordCommandFactory $editRecordCommandFactory,
        EditRecordHandler $editRecordHandler,
        CreateRecordHandler $createRecordHandler,
        Router $router,
        ValidatorInterface $recordDataValidator
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->recordExists = $recordExists;
        $this->editRecordCommandFactory = $editRecordCommandFactory;
        $this->editRecordHandler = $editRecordHandler;
        $this->createRecordHandler = $createRecordHandler;
        $this->router = $router;
        $this->recordDataValidator = $recordDataValidator;
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
            $responsesData[] = $this->createOrUpdateRecord($referenceEntityIdentifier, $normalizedRecord);
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
        $responseData = ['code' => $normalizedRecord['code']];

        try {
            if (!$this->recordExists->withReferenceEntityAndCode($referenceEntityIdentifier, $recordCode)) {
                $responseData['status_code'] = Response::HTTP_CREATED;
                $this->createRecord($referenceEntityIdentifier, $normalizedRecord);
            } else {
                $responseData['status_code'] = Response::HTTP_NO_CONTENT;
                $this->updateRecord($referenceEntityIdentifier, $normalizedRecord);
            }
        } catch (\InvalidArgumentException | ViolationHttpException $exception) {
            $responseData['status_code'] = Response::HTTP_UNPROCESSABLE_ENTITY;
            $responseData['message'] = $exception->getMessage();
            // TODO: format violations errors for the ViolationHttpException
        }

        return $responseData;
    }

    private function createRecord(ReferenceEntityIdentifier $referenceEntityIdentifier, array $normalizedRecord): void
    {
        $createRecordCommand = $this->createValidatedRecordCommand($referenceEntityIdentifier, $normalizedRecord);
        $editRecordCommand = $this->createValidatedEditCommand($referenceEntityIdentifier, $normalizedRecord);

        ($this->createRecordHandler)($createRecordCommand);
        ($this->editRecordHandler)($editRecordCommand);
    }

    private function updateRecord(ReferenceEntityIdentifier $referenceEntityIdentifier, array $normalizedRecord): void
    {
        $editRecordCommand = $this->createValidatedEditCommand($referenceEntityIdentifier, $normalizedRecord);
        ($this->editRecordHandler)($editRecordCommand);
    }

    private function createValidatedRecordCommand(ReferenceEntityIdentifier $referenceEntityIdentifier, array $normalizedRecord): CreateRecordCommand
    {
        $command = new CreateRecordCommand();
        $command->code = $normalizedRecord['code'];
        $command->referenceEntityIdentifier = $referenceEntityIdentifier->normalize();
        $command->labels = [];

        $violations = $this->recordDataValidator->validate($command);
        if ($violations->count() > 0) {
            throw new ViolationHttpException($violations, 'The record has data that does not comply with the business rules.');
        }

        return $command;
    }

    private function createValidatedEditCommand(ReferenceEntityIdentifier $referenceEntityIdentifier, array $normalizedRecord): EditRecordCommand
    {
        try {
            $editRecordCommand = $this->editRecordCommandFactory->create($referenceEntityIdentifier, $normalizedRecord);
        } catch (\InvalidArgumentException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $violations = $this->recordDataValidator->validate($editRecordCommand);
        if ($violations->count() > 0) {
            throw new ViolationHttpException($violations, 'The record has data that does not comply with the business rules.');
        }

        return $editRecordCommand;
    }
}

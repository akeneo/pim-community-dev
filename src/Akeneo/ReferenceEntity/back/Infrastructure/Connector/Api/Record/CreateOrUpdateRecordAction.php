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
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\JsonSchemaErrorsFormatter;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\RecordValidator;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateOrUpdateRecordAction
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

    /** @var RecordValidator */
    private $recordStructureValidator;

    /** @var ValidatorInterface */
    private $recordDataValidator;

    public function __construct(
        ReferenceEntityExistsInterface $referenceEntityExists,
        RecordExistsInterface $recordExists,
        EditRecordCommandFactory $editRecordCommandFactory,
        EditRecordHandler $editRecordHandler,
        CreateRecordHandler $createRecordHandler,
        Router $router,
        RecordValidator $recordStructureValidator,
        ValidatorInterface $recordDataValidator
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->recordExists = $recordExists;
        $this->editRecordCommandFactory = $editRecordCommandFactory;
        $this->editRecordHandler = $editRecordHandler;
        $this->createRecordHandler = $createRecordHandler;
        $this->router = $router;
        $this->recordStructureValidator = $recordStructureValidator;
        $this->recordDataValidator = $recordDataValidator;
    }

    public function __invoke(Request $request, string $referenceEntityIdentifier, string $code): Response
    {
        try {
            $recordCode = RecordCode::fromString($code);
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        if (!$this->referenceEntityExists->withIdentifier($referenceEntityIdentifier)) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier));
        }

        $normalizedRecord = $this->getNormalizedRecordFromRequest($request);
        $structureErrors = $this->recordStructureValidator->validate($referenceEntityIdentifier, $normalizedRecord);

        if (!empty($structureErrors)) {
            return new JsonResponse([
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'The record has an invalid format.',
                'errors' => JsonSchemaErrorsFormatter::format($structureErrors),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $createRecordCommand = $this->createValidatedRecordCommandIfNeeded($referenceEntityIdentifier, $recordCode);
        $editRecordCommand = $this->createValidatedEditCommand($referenceEntityIdentifier, $normalizedRecord);

        $responseStatusCode = Response::HTTP_NO_CONTENT;

        if (null !== $createRecordCommand) {
            $responseStatusCode = Response::HTTP_CREATED;
            ($this->createRecordHandler)($createRecordCommand);
        }

        ($this->editRecordHandler)($editRecordCommand);

        return $this->createResponse($responseStatusCode, $referenceEntityIdentifier, $recordCode);
    }

    private function createValidatedRecordCommandIfNeeded(ReferenceEntityIdentifier $referenceEntityIdentifier, RecordCode $recordCode): ?CreateRecordCommand
    {
        if ($this->recordExists->withReferenceEntityAndCode($referenceEntityIdentifier, $recordCode)) {
            return null;
        }

        $command = new CreateRecordCommand();
        $command->code = $recordCode->normalize();
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

    private function getNormalizedRecordFromRequest(Request $request): array
    {
        $normalizedRecord = json_decode($request->getContent(), true);

        if (null === $normalizedRecord) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        $code = $request->get('code');
        $codeInBody = $normalizedRecord['code'] ?? null;
        if ($codeInBody !== $code) {
            throw new UnprocessableEntityHttpException('The code of the record provided in the URI must be the same as the one provided in the request body.');
        }

        return $normalizedRecord;
    }

    private function createResponse(int $responseStatusCode, ReferenceEntityIdentifier $referenceEntityIdentifier, RecordCode $recordCode): Response
    {
        $headers = [
            'location' => $this->router->generate('akeneo_reference_entities_record_rest_connector_get', [
                'referenceEntityIdentifier' => $referenceEntityIdentifier->normalize(),
                'code' => $recordCode->normalize(),
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        return Response::create('', $responseStatusCode, $headers);
    }
}

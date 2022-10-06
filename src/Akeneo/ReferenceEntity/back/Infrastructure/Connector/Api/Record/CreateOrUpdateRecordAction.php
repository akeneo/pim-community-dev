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
use Akeneo\ReferenceEntity\Domain\Exception\RecordAlreadyExistsError;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordExistsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\JsonSchemaErrorsFormatter;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\RecordValidator;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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
    public function __construct(
        private ReferenceEntityExistsInterface $referenceEntityExists,
        private RecordExistsInterface $recordExists,
        private EditRecordCommandFactory $editRecordCommandFactory,
        private EditRecordHandler $editRecordHandler,
        private CreateRecordHandler $createRecordHandler,
        private Router $router,
        private RecordValidator $recordStructureValidator,
        private ValidatorInterface $recordDataValidator,
        private SecurityFacade $securityFacade,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(Request $request, string $referenceEntityIdentifier, string $code): Response
    {
        $this->denyAccessUnlessAclIsGranted();

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
            try {
                ($this->createRecordHandler)($createRecordCommand);
                $responseStatusCode = Response::HTTP_CREATED;
            } catch (RecordAlreadyExistsError) {
                $this->logger->notice('Concurrent record creation call have been detected', [
                    'reference_entity_identifier' => $referenceEntityIdentifier,
                    'record_code' => $recordCode
                ]);
            }
        }

        ($this->editRecordHandler)($editRecordCommand);

        return $this->createResponse($responseStatusCode, $referenceEntityIdentifier, $recordCode);
    }

    private function createValidatedRecordCommandIfNeeded(ReferenceEntityIdentifier $referenceEntityIdentifier, RecordCode $recordCode): ?CreateRecordCommand
    {
        if ($this->recordExists->withReferenceEntityAndCode($referenceEntityIdentifier, $recordCode)) {
            return null;
        }

        $command = new CreateRecordCommand(
            $referenceEntityIdentifier->normalize(),
            $recordCode->normalize(),
            []
        );

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

        return new Response('', $responseStatusCode, $headers);
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        if (!$this->securityFacade->isGranted('pim_api_reference_entity_record_edit')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to create or update reference entity records.');
        }
    }
}

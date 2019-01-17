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

namespace Akeneo\ReferenceEntity\Acceptance\Context;

use Akeneo\ReferenceEntity\Application\Record\DeleteAllRecords\DeleteAllReferenceEntityRecordsCommand;
use Akeneo\ReferenceEntity\Application\Record\DeleteAllRecords\DeleteAllReferenceEntityRecordsHandler;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityHandler;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryRecordRepository;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class DeleteAllRecordsContext implements Context
{
    private const REFERENCE_ENTITY_IDENTIFIER_FIRST = 'designer';
    private const REFERENCE_ENTITY_IDENTIFIER_SECOND = 'brand';

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var InMemoryRecordRepository */
    private $recordRepository;

    /** @var DeleteAllReferenceEntityRecordsHandler */
    private $deleteAllRecordsHandler;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ExceptionContext */
    private $exceptionContext;

    /** @var ConstraintViolationsContext */
    private $violationsContext;

    /** @var CreateReferenceEntityHandler */
    private $createReferenceEntityHandler;

    public function __construct(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        RecordRepositoryInterface $recordRepository,
        DeleteAllReferenceEntityRecordsHandler $deleteAllRecordsHandler,
        ValidatorInterface $validator,
        ConstraintViolationsContext $violationsContext,
        ExceptionContext $exceptionContext,
        CreateReferenceEntityHandler $createReferenceEntityHandler
    ) {
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->recordRepository = $recordRepository;
        $this->deleteAllRecordsHandler = $deleteAllRecordsHandler;
        $this->exceptionContext = $exceptionContext;
        $this->validator = $validator;
        $this->violationsContext = $violationsContext;
        $this->createReferenceEntityHandler = $createReferenceEntityHandler;
    }

    /**
     * @Given /^two reference entities with two records each$/
     * @throws \Exception
     */
    public function twoReferenceEntitiesWithTwoRecordsEach()
    {
        $this->createReferenceEntity(self::REFERENCE_ENTITY_IDENTIFIER_FIRST);
        $this->createRecord(self::REFERENCE_ENTITY_IDENTIFIER_FIRST);
        $this->createRecord(self::REFERENCE_ENTITY_IDENTIFIER_FIRST);

        $this->createReferenceEntity(self::REFERENCE_ENTITY_IDENTIFIER_SECOND);
        $this->createRecord(self::REFERENCE_ENTITY_IDENTIFIER_SECOND);
        $this->createRecord(self::REFERENCE_ENTITY_IDENTIFIER_SECOND);
    }

    /**
     * @When /^the user deletes all the records from one reference entity$/
     */
    public function theUserDeletesAllTheRecordFromOneEntity(): void
    {
        $command = new DeleteAllReferenceEntityRecordsCommand();
        $command->referenceEntityIdentifier = self::REFERENCE_ENTITY_IDENTIFIER_FIRST;

        $this->executeCommand($command);
    }

    /**
     * @When /^the user deletes all the records from an unknown entity$/
     */
    public function theUserDeletesAllTheRecordFromUnknownEntity(): void
    {
        $command = new DeleteAllReferenceEntityRecordsCommand();
        $command->referenceEntityIdentifier = 'unknown';

        $this->executeCommand($command);
    }

    /**
     * @When /^there should be no records for this reference entity$/
     */
    public function thereShouldBeNoRecordForThisEntity(): void
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER_FIRST);
        Assert::assertFalse($this->recordRepository->referenceEntityHasRecords($referenceEntityIdentifier));
    }

    /**
     * @When /^there is still two records on the other reference entity$/
     */
    public function thereIsStillTwoRecordsForTheOtherEntity(): void
    {
        $this->violationsContext->assertThereIsNoViolations();
        $this->exceptionContext->thereIsNoExceptionThrown();

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER_SECOND);
        Assert::assertEquals(2, $this->recordRepository->countByReferenceEntity($referenceEntityIdentifier));
    }

    /**
     * @When /^there is still two records for each reference entity$/
     */
    public function thereIsStillTwoRecordsForEachEntity(): void
    {
        $this->violationsContext->assertThereIsNoViolations();
        $this->exceptionContext->thereIsNoExceptionThrown();

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER_FIRST);
        Assert::assertEquals(2, $this->recordRepository->countByReferenceEntity($referenceEntityIdentifier));
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER_SECOND);
        Assert::assertEquals(2, $this->recordRepository->countByReferenceEntity($referenceEntityIdentifier));
    }

    private function createReferenceEntity(string $identifier): void
    {
        $createCommand = new CreateReferenceEntityCommand();
        $createCommand->code = $identifier;
        $createCommand->labels = [];

        $violations = $this->validator->validate($createCommand);
        if ($violations->count() > 0) {
            throw new \LogicException(sprintf('Cannot create reference entity: %s', $violations->get(0)->getMessage()));
        }

        ($this->createReferenceEntityHandler)($createCommand);
    }

    private function createRecord(string $referenceEntityIdentifier): void
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        $recordCode = RecordCode::fromString(str_replace('-', '', Uuid::uuid4()->toString()));
        $recordIdentifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode);
        $this->recordRepository->create(Record::create(
            $recordIdentifier,
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([])
        ));
    }

    private function executeCommand(DeleteAllReferenceEntityRecordsCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            $this->violationsContext->addViolations($violations);

            return;
        }

        try {
            ($this->deleteAllRecordsHandler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }
}

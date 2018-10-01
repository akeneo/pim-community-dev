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

use Akeneo\ReferenceEntity\Application\Record\DeleteRecord\DeleteRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\DeleteRecord\DeleteRecordHandler;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryRecordRepository;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class DeleteRecordContext implements Context
{
    private const REFERENCE_ENTITY_IDENTIFIER = 'designer';
    private const FINGERPRINT = 'fingerprint';
    private const RECORD_CODE = 'stark';

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var InMemoryRecordRepository */
    private $recordRepository;

    /** @var DeleteRecordHandler */
    private $deleteRecordHandler;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ExceptionContext */
    private $exceptionContext;

    /** @var ConstraintViolationsContext */
    private $violationsContext;

    public function __construct(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        RecordRepositoryInterface $recordRepository,
        DeleteRecordHandler $deleteRecordHandler,
        ValidatorInterface $validator,
        ConstraintViolationsContext $violationsContext,
        ExceptionContext $exceptionContext
    ) {
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->recordRepository = $recordRepository;
        $this->deleteRecordHandler = $deleteRecordHandler;
        $this->exceptionContext = $exceptionContext;
        $this->validator = $validator;
        $this->violationsContext = $violationsContext;
    }

    /**
     * @Given /^an reference entity with one record$/
     * @throws \Exception
     */
    public function anReferenceEntityWithTwoRecords()
    {
        $this->createReferenceEntity();
        $this->createRecord();
    }

    /**
     * @When /^the user deletes the record$/
     */
    public function theUserDeletesTheRecord(): void
    {
        $command = new DeleteRecordCommand();
        $command->recordCode = self::RECORD_CODE;
        $command->referenceEntityIdentifier = self::REFERENCE_ENTITY_IDENTIFIER;

        $this->executeDeleteCommand($command);
    }

    /**
     * @When /^the user tries to delete record that does not exist$/
     */
    public function theUserDeletesAWrongRecord(): void
    {
        $recordCode = 'unknown_code';

        $command = new DeleteRecordCommand();
        $command->recordCode = $recordCode;
        $command->referenceEntityIdentifier = self::REFERENCE_ENTITY_IDENTIFIER;

        $this->executeDeleteCommand($command);
    }

    /**
     * @Then /^the record should not exist anymore$/
     */
    public function theRecordShouldNotExist()
    {
        try {
            $this->recordRepository->getByReferenceEntityAndCode(
                ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
                RecordCode::fromString(self::RECORD_CODE)
            );
        } catch (RecordNotFoundException $exception) {
            return;
        }

        Assert::true(false, 'The record should not exist');
    }

    private function createReferenceEntity(): void
    {
        $this->referenceEntityRepository->create(ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
            [],
            Image::createEmpty()
        ));
    }

    private function createRecord(): void
    {
        $this->recordRepository->create(Record::create(
            RecordIdentifier::create(self::REFERENCE_ENTITY_IDENTIFIER, self::RECORD_CODE, self::FINGERPRINT),
            ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
            RecordCode::fromString(self::RECORD_CODE),
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        ));
    }

    private function executeDeleteCommand(DeleteRecordCommand $deleteRecordCommand): void
    {
        $violations = $this->validator->validate($deleteRecordCommand);
        if ($violations->count() > 0) {
            $this->violationsContext->addViolations($violations);

            return;
        }

        try {
            ($this->deleteRecordHandler)($deleteRecordCommand);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }
}

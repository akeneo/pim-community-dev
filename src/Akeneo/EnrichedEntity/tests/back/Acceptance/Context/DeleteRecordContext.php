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

namespace Akeneo\EnrichedEntity\Acceptance\Context;

use Akeneo\EnrichedEntity\Application\Record\DeleteRecord\DeleteRecordCommand;
use Akeneo\EnrichedEntity\Application\Record\DeleteRecord\DeleteRecordHandler;
use Akeneo\EnrichedEntity\Common\Fake\InMemoryRecordRepository;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepositoryInterface;
use Akeneo\EnrichedEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class DeleteRecordContext implements Context
{
    private const ENRICHED_ENTITY_IDENTIFIER = 'designer';
    private const FINGERPRINT = 'fingerprint';
    private const RECORD_CODE = 'stark';

    /** @var EnrichedEntityRepositoryInterface */
    private $enrichedEntityRepository;

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
        EnrichedEntityRepositoryInterface $enrichedEntityRepository,
        RecordRepositoryInterface $recordRepository,
        DeleteRecordHandler $deleteRecordHandler,
        ValidatorInterface $validator,
        ConstraintViolationsContext $violationsContext,
        ExceptionContext $exceptionContext
    ) {
        $this->enrichedEntityRepository = $enrichedEntityRepository;
        $this->recordRepository = $recordRepository;
        $this->deleteRecordHandler = $deleteRecordHandler;
        $this->exceptionContext = $exceptionContext;
        $this->validator = $validator;
        $this->violationsContext = $violationsContext;
    }

    /**
     * @Given /^an enriched entity with one record$/
     * @throws \Exception
     */
    public function anEnrichedEntityWithTwoRecords()
    {
        $this->createEnrichedEntity();
        $this->createRecord();
    }

    /**
     * @When /^the user deletes the record$/
     */
    public function theUserDeletesTheRecord(): void
    {
        $command = new DeleteRecordCommand();
        $command->recordCode = self::RECORD_CODE;
        $command->enrichedEntityIdentifier = self::ENRICHED_ENTITY_IDENTIFIER;

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
        $command->enrichedEntityIdentifier = self::ENRICHED_ENTITY_IDENTIFIER;

        $this->executeDeleteCommand($command);
    }

    /**
     * @Then /^the record should not exist anymore$/
     */
    public function theRecordShouldNotExist()
    {
        try {
            $this->recordRepository->getByEnrichedEntityAndCode(
                EnrichedEntityIdentifier::fromString(self::ENRICHED_ENTITY_IDENTIFIER),
                RecordCode::fromString(self::RECORD_CODE)
            );
        } catch (RecordNotFoundException $exception) {
            return;
        }

        Assert::true(false, 'The record should not exist');
    }

    private function createEnrichedEntity(): void
    {
        $this->enrichedEntityRepository->create(EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString(self::ENRICHED_ENTITY_IDENTIFIER),
            [],
            Image::createEmpty()
        ));
    }

    private function createRecord(): void
    {
        $this->recordRepository->create(Record::create(
            RecordIdentifier::create(self::ENRICHED_ENTITY_IDENTIFIER, self::RECORD_CODE, self::FINGERPRINT),
            EnrichedEntityIdentifier::fromString(self::ENRICHED_ENTITY_IDENTIFIER),
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

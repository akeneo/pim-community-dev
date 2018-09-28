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
    private const RECORD_CODE_ONE = 'stark';
    private const RECORD_CODE_TWO = 'dyson';

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
     * @Given /^an enriched entity with two records$/
     * @throws \Exception
     */
    public function anEnrichedEntityWithTwoRecords()
    {
        $this->createEnrichedEntity();
        $this->createRecord(self::RECORD_CODE_ONE);
        $this->createRecord(self::RECORD_CODE_TWO);
    }

    /**
     * @When /^the user deletes the (first|second) record$/
     */
    public function theUserDeletesTheRecord(string $whichRecord): void
    {
        $recordCode = ('first' === $whichRecord) ? self::RECORD_CODE_ONE : self::RECORD_CODE_TWO;

        $command = new DeleteRecordCommand();
        $command->recordCode = $recordCode;
        $command->enrichedEntityIdentifier = self::ENRICHED_ENTITY_IDENTIFIER;

        $this->executeDeleteCommand($command);
    }

    /**
     * @When /^the user deletes a wrong record$/
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
     * @Then /^the (first|second) record should not exist anymore$/
     */
    public function theRecordShouldNotExist(string $whichRecord)
    {
        $recordCode = ('first' === $whichRecord) ? self::RECORD_CODE_ONE : self::RECORD_CODE_TWO;

        try {
            $this->recordRepository->getByEnrichedEntityAndCode(
                EnrichedEntityIdentifier::fromString(self::ENRICHED_ENTITY_IDENTIFIER),
                RecordCode::fromString($recordCode)
            );
        } catch (RecordNotFoundException $e) {
            return;
        }

        Assert::true(false, sprintf('The %s record should not exist', $whichRecord));
    }

    private function createEnrichedEntity(): void
    {
        $this->enrichedEntityRepository->create(EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString(self::ENRICHED_ENTITY_IDENTIFIER),
            [],
            Image::createEmpty()
        ));
    }

    private function createRecord(string $recordCode): void
    {
        $this->recordRepository->create(Record::create(
            RecordIdentifier::create(self::ENRICHED_ENTITY_IDENTIFIER, $recordCode, self::FINGERPRINT),
            EnrichedEntityIdentifier::fromString(self::ENRICHED_ENTITY_IDENTIFIER),
            RecordCode::fromString($recordCode),
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

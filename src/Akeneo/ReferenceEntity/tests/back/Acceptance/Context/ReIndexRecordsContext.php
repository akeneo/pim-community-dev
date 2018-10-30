<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Acceptance\Context;

use Akeneo\ReferenceEntity\Application\Record\IndexRecords\IndexRecordsByReferenceEntity;
use Akeneo\ReferenceEntity\Application\Record\IndexRecords\IndexRecordsByReferenceEntityCommand;
use Akeneo\ReferenceEntity\Common\Fake\RecordIndexerSpy;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordIndexerInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ReIndexRecordsContext implements Context
{
    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var IndexRecordsByReferenceEntity */
    private $indexRecordsByReferenceEntity;

    /** @var RecordIndexerInterface */
    private $recordIndexerSpy;

    /** @var ConstraintViolationsContext */
    private $constraintViolationsContext;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        IndexRecordsByReferenceEntity $indexRecordsByReferenceEntity,
        ValidatorInterface $validator,
        ConstraintViolationsContext $constraintViolationsContext,
        RecordIndexerSpy $recordIndexerSpy
    ) {
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->indexRecordsByReferenceEntity = $indexRecordsByReferenceEntity;
        $this->validator = $validator;
        $this->constraintViolationsContext = $constraintViolationsContext;
        $this->recordIndexerSpy = $recordIndexerSpy;
    }

    /**
     * @Given /^the reference entity "([^"]*)"$/
     */
    public function theReferenceEntity(string $referenceEntityIdentifier): void
    {
        $this->referenceEntityRepository->create(
            ReferenceEntity::create(
                ReferenceEntityIdentifier::fromString($referenceEntityIdentifier), [], Image::createEmpty()
            )
        );
    }
    /**
     * @Given /^none of the records of "([^"]*)" are indexed$/
     */
    public function noneOfTheRecordsOfAreIndexed(string $referenceEntityIdentifier)
    {
        $this->recordIndexerSpy->assertReferenceEntityNotIndexed($referenceEntityIdentifier);
    }

    /**
     * @When /^the system administrator reindexes all the records of "([^"]*)"$/
     */
    public function theSystemAdministratorReindexesAllTheRecordsOf(string $refenceEntityIdentifier): void
    {
        $command = new IndexRecordsByReferenceEntityCommand();
        $command->referenceEntityIdentifier = $refenceEntityIdentifier;
        $violations = $this->validator->validate($command);

        if (0 < $violations->count()) {
            $this->constraintViolationsContext->addViolations($violations);

            return;
        }
        ($this->indexRecordsByReferenceEntity)($command);
    }

    /**
     * @Then /^the records of the reference entity "([^"]*)" have been indexed$/
     */
    public function theRecordsOfTheReferenceEntityHaveBeenIndexed(string $referenceEntityIdentifier): void
    {
        $this->recordIndexerSpy->assertReferenceEntityIndexed($referenceEntityIdentifier);
    }

    /**
     * @When /^the system administrator reindexes the records of a reference entity that does not exist$/
     */
    public function theSystemAdministratorReindexesTheRecordsOfAnReferenceEntityThatDoesNotExist()
    {
        $command = new IndexRecordsByReferenceEntityCommand();
        $command->referenceEntityIdentifier = 'unknown_reference_entity';
        $violations = $this->validator->validate($command);

        if (0 < $violations->count()) {
            $this->constraintViolationsContext->addViolations($violations);

            return;
        }
        ($this->indexRecordsByReferenceEntity)($command);
    }
}

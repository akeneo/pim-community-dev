<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Acceptance\Context;

use Akeneo\ReferenceEntity\Application\Record\IndexRecords\IndexRecordsByReferenceEntityCommand;
use Akeneo\ReferenceEntity\Application\Record\IndexRecords\IndexRecordsByReferenceEntityHandler;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityHandler;
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
class IndexRecordsContext implements Context
{
    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var IndexRecordsByReferenceEntityHandler */
    private $indexRecordsByReferenceEntity;

    /** @var RecordIndexerInterface */
    private $recordIndexerSpy;

    /** @var ConstraintViolationsContext */
    private $constraintViolationsContext;

    /** @var ValidatorInterface */
    private $validator;

    /** @var CreateReferenceEntityHandler */
    private $createReferenceEntityHandler;

    public function __construct(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        IndexRecordsByReferenceEntityHandler $indexRecordsByReferenceEntity,
        ValidatorInterface $validator,
        ConstraintViolationsContext $constraintViolationsContext,
        RecordIndexerSpy $recordIndexerSpy,
        CreateReferenceEntityHandler $createReferenceEntityHandler
    ) {
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->indexRecordsByReferenceEntity = $indexRecordsByReferenceEntity;
        $this->validator = $validator;
        $this->constraintViolationsContext = $constraintViolationsContext;
        $this->recordIndexerSpy = $recordIndexerSpy;
        $this->createReferenceEntityHandler = $createReferenceEntityHandler;
    }

    /**
     * @Given /^the reference entity "([^"]*)"$/
     */
    public function theReferenceEntity(string $referenceEntityIdentifier): void
    {
        $createCommand = new CreateReferenceEntityCommand($referenceEntityIdentifier, []);

        $violations = $this->validator->validate($createCommand);
        if ($violations->count() > 0) {
            throw new \LogicException(sprintf('Cannot create reference entity: %s', $violations->get(0)->getMessage()));
        }

        ($this->createReferenceEntityHandler)($createCommand);
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
        $command = new IndexRecordsByReferenceEntityCommand($refenceEntityIdentifier);
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
        $command = new IndexRecordsByReferenceEntityCommand('unknown_reference_entity');
        $violations = $this->validator->validate($command);

        if (0 < $violations->count()) {
            $this->constraintViolationsContext->addViolations($violations);

            return;
        }
        ($this->indexRecordsByReferenceEntity)($command);
    }
}

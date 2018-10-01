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

use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordHandler;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class CreateRecordContext implements Context
{
    /** @var CreateRecordHandler */
    private $createRecordHandler;

    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /** @var ExceptionContext */
    private $exceptionContext;

    public function __construct(
        RecordRepositoryInterface $recordRepository,
        CreateRecordHandler $createRecordHandler,
        ExceptionContext $exceptionContext
    ) {
        $this->createRecordHandler = $createRecordHandler;
        $this->recordRepository = $recordRepository;
        $this->exceptionContext = $exceptionContext;
    }

    /**
     * @When /^the user creates a record "([^"]+)" for entity "([^"]+)" with:$/
     */
    public function theUserCreatesARecordWith(
        string $code,
        string $referenceEntityIdentifier,
        TableNode $updateTable
    ) {
        $updates = current($updateTable->getHash());
        $command = new CreateRecordCommand();
        $command->code = $code;
        $command->referenceEntityIdentifier = $referenceEntityIdentifier;
        $command->labels = json_decode($updates['labels'], true);
        try {
            ($this->createRecordHandler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Then /^there is a record with:$/
     */
    public function thereIsARecordWith(TableNode $referenceEntityTable)
    {
        $expectedInformation = current($referenceEntityTable->getHash());
        $expectedIdentifier = $this->recordRepository->nextIdentifier(
            ReferenceEntityIdentifier::fromString($expectedInformation['entity_identifier']),
            RecordCode::fromString($expectedInformation['code'])
        );
        $actualReferenceEntity = $this->recordRepository->getByIdentifier($expectedIdentifier);
        $this->assertSameLabels(
            json_decode($expectedInformation['labels'], true),
            $actualReferenceEntity
        );
    }

    private function assertSameLabels(array $expectedLabels, Record $record)
    {
        $actualLabels = [];
        foreach ($record->getLabelCodes() as $labelCode) {
            $actualLabels[$labelCode] = $record->getLabel($labelCode);
        }

        $differences = array_merge(
            array_diff($expectedLabels, $actualLabels),
            array_diff($actualLabels, $expectedLabels)
        );

        Assert::isEmpty(
            $differences,
            sprintf('Expected labels "%s", but found %s', json_encode($expectedLabels), json_encode($actualLabels))
        );
    }

    /**
     * @Given /^there should be no record$/
     */
    public function thereShouldBeNoRecord()
    {
        $referenceEntityCount = $this->recordRepository->count();
        Assert::same(
            0,
            $referenceEntityCount,
            sprintf('Expected to have 0 reference entity. %d found.', $referenceEntityCount)
        );
    }
}

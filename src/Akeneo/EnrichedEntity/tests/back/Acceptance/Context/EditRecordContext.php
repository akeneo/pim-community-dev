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

namespace Akeneo\EnrichedEntity\tests\back\Acceptance\Context;

use Akeneo\EnrichedEntity\Application\Record\EditRecord\EditRecordCommand;
use Akeneo\EnrichedEntity\Application\Record\EditRecord\EditRecordHandler;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\EnrichedEntity\tests\back\Common\Fake\InMemoryAttributeRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class EditRecordContext implements Context
{
    /** @var EditRecordHandler */
    private $editRecordHandler;

    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /** @var AttributeRepositoryInterface | InMemoryAttributeRepository */
    private $attributeRepository;

    /** @var ExceptionContext */
    private $exceptionContext;

    public function __construct(
        RecordRepositoryInterface $recordRepository,
        AttributeRepositoryInterface $attributeRepository,
        EditRecordHandler $editRecordHandler,
        ExceptionContext $exceptionContext
    ) {
        $this->editRecordHandler = $editRecordHandler;
        $this->recordRepository = $recordRepository;
        $this->exceptionContext = $exceptionContext;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @Given /^the following records for the enriched entity "(.+)":$/
     */
    public function theFollowingRecords(string $entityIdentifier, TableNode $recordsTable)
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString($entityIdentifier);

        foreach ($recordsTable->getHash() as $record) {
            $values = isset($record['values']) ? json_decode($record['values'], true) : [];
            var_dump($values);
            $this->recordRepository->create(Record::create(
                RecordIdentifier::fromString($record['identifier']),
                $enrichedEntityIdentifier,
                RecordCode::fromString($record['code']),
                json_decode($record['labels'], true),
                ValueCollection::fromValues($values)
            ));
        }
    }

    /**
     * @When /^the user updates the values of record "([^"]+)" with:$/
     */
    public function theUserUpdatesTheRecordValuesWith(string $identifier, TableNode $updateTable)
    {
        $actualRecord = $this->recordRepository->getByIdentifier(RecordIdentifier::fromString($identifier));

        $command = new EditRecordCommand();
        $command->identifier = $identifier;
        $command->enrichedEntityIdentifier = (string) $actualRecord->getEnrichedEntityIdentifier();
        $command->labels = [];

        $values = [];
        foreach ($updateTable->getHash() as $rowValues) {
            $values[] = json_decode($rowValues['data'], true);
        }
        $command->values = $values;
        ($this->editRecordHandler)($command);
    }

    /**
     * @When /^the user updates the record "([^"]+)" with:$/
     */
    public function theUserUpdatesTheRecordWith(string $identifier, TableNode $updateTable)
    {
        $actualRecord = $this->recordRepository->getByIdentifier(RecordIdentifier::fromString($identifier));

        $command = new EditRecordCommand();
        $command->identifier = $identifier;
        $command->enrichedEntityIdentifier = (string) $actualRecord->getEnrichedEntityIdentifier();

        $updates = $updateTable->getRowsHash();
        $command->labels = isset($updates['labels']) ? json_decode($updates['labels'], true) : [];
        $command->values = isset($updates['values']) ? json_decode($updates['values'], true) : [];

        ($this->editRecordHandler)($command);
    }

    /**
     * @Then /^the record "([^"]+)" should be:$/
     */
    public function theRecordShouldBe(string $identifier, TableNode $enrichedEntityTable)
    {
        $expectedIdentifier = RecordIdentifier::fromString($identifier);
        $expectedInformation = current($enrichedEntityTable->getHash());
        $actualRecord = $this->recordRepository->getByIdentifier($expectedIdentifier);

        $this->assertSameLabels(
            json_decode($expectedInformation['labels'], true),
            $actualRecord
        );
    }

    /**
     * @Then /^the values of record "([^"]+)" should be:$/
     */
    public function theRecordValuesShouldBe(string $identifier, TableNode $updateTable)
    {
        $expectedIdentifier = RecordIdentifier::fromString($identifier);
        $actualRecord = $this->recordRepository->getByIdentifier($expectedIdentifier);

        $expectedValues = [];
        foreach ($updateTable->getHash() as $value) {
            $expectedValues[] = json_decode($value['data'], true);
        }

        $notFound = [];
        foreach ($expectedValues as $expectedValue) {
            if (!$this->recordHasValue($expectedValue, $actualRecord)) {
                $notFound[] = $expectedValue;
            }
        }

        Assert::isEmpty(
            $notFound,
            sprintf('Expected values "%s" not found', json_encode($notFound))
        );
    }

    private function assertSameLabels(array $expectedLabels, Record $actualRecord)
    {
        $actualLabels = [];
        foreach ($actualRecord->getLabelCodes() as $labelCode) {
            $actualLabels[$labelCode] = $actualRecord->getLabel($labelCode);
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

    private function recordHasValue(array $expectedValue, Record $actualRecord): bool
    {
        $actualValues = $actualRecord->getValues()->normalize();

        foreach ($actualValues as $actualValue) {
            $differences = array_diff($actualValue, $expectedValue);
            if (empty($differences)) {
                return true;
            }
        }

        return false;
    }
}

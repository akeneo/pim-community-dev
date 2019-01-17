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

use Akeneo\ReferenceEntity\Application\Record\SearchRecord\SearchRecord;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersForQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Domain\Query\Record\SearchRecordResult;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class ListRecordContext implements Context
{
    /** @var SearchRecordResult */
    private $result;

    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /** @var ReferenceEntityRepositoryInterface  */
    private $referenceEntityRepository;

    /** @var FindIdentifiersForQueryInterface */
    private $findIdentifiersForQuery;

    /** @var SearchRecord */
    private $searchRecord;

    public function __construct(
        RecordRepositoryInterface $recordRepository,
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        FindIdentifiersForQueryInterface $findIdentifiersForQuery,
        SearchRecord $searchRecord
    ) {
        $this->recordRepository = $recordRepository;
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->findIdentifiersForQuery = $findIdentifiersForQuery;
        $this->searchRecord = $searchRecord;
    }

    /**
     * @Given the records :recordCodes
    */
    public function theRecords($recordCodes)
    {
        $this->loadReferenceEntity();

        array_map(function (string $recordCode) {
            $this->loadRecord($recordCode);
        }, explode(',', $recordCodes));
    }

    /**
     * @When the user search for :searchInput
    */
    public function theUserSearchFor($searchInput)
    {
        $query = RecordQuery::createFromNormalized([
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'size' => 20,
            'page' => 0,
            'filters' => [
                [
                    'field' => 'full_text',
                    'operator' => '=',
                    'value' => $searchInput,
                    'context' => []
                ],
                [
                    'field' => 'reference_entity',
                    'operator' => '=',
                    'value' => 'designer',
                    'context' => []
                ]
            ]
        ]);

        $this->result = ($this->searchRecord)($query);
    }

    /**
     * @When /^the user filters records by "([^"]+)" with operator "([^"]+)" and value "([^"]*)"$/
     */
    public function theUserFiltersRecordsByWithOperatorAndValue($filter, $operator, $value)
    {
        $query = RecordQuery::createFromNormalized([
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'size' => 20,
            'page' => 0,
            'filters' => [
                [
                    'field' => $filter,
                    'operator' => $operator,
                    'value' => $value,
                    'context' => []
                ],
                [
                    'field' => 'reference_entity',
                    'operator' => '=',
                    'value' => 'designer',
                    'context' => []
                ]
            ]
        ]);

        $this->result = ($this->searchRecord)($query);
    }

    /**
     * @Then the search result should be :recordCodes
     */
    public function theSearchResultShouldBe(string $expectedRecordCodes)
    {
        $expectedRecordCodes = explode(',', $expectedRecordCodes);
        $resultCodes = array_map(
            function (RecordItem $recordItem): string {
                return $recordItem->code;
            },
            $this->result->items
        );

        array_map(function (string $expectedRecordCode) use ($resultCodes) {
            Assert::assertContains($expectedRecordCode, $resultCodes);
        }, $expectedRecordCodes);

        Assert::assertCount(count($expectedRecordCodes), $resultCodes, 'More results found than expected');
    }

    /**
     * @Then /^there should be no result on a total of (\d+) records$/
     */
    public function thereShouldBeNoResult(int $expectedTotalOfRecords)
    {
        Assert::assertEquals(0, $this->result->matchesCount);
        Assert::assertEmpty($this->result->items);
        Assert::assertEquals($expectedTotalOfRecords, $this->result->totalCount);
    }

    /**
     * @When the user list the records
    */
    public function theUserListTheRecords()
    {
        $query = RecordQuery::createFromNormalized([
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'size' => 20,
            'page' => 0,
            'filters' => [
                [
                    'field' => 'full_text',
                    'operator' => '=',
                    'value' => '',
                    'context' => []
                ],
                [
                    'field' => 'reference_entity',
                    'operator' => '=',
                    'value' => 'designer',
                    'context' => []
                ]
            ]
        ]);

        $this->result = ($this->searchRecord)($query);
    }

    private function loadRecord(string $recordCode): void
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntity = $this->referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
        $attributeAsLabel = $referenceEntity->getAttributeAsLabelReference();
        $identifier = RecordIdentifier::fromString($recordCode . '_fingerprint');

        $labelValue = Value::create(
            $attributeAsLabel->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString(ucfirst((string) $recordCode))
        );
        $recordCode = RecordCode::fromString($recordCode);
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([$labelValue])
        );
        $this->recordRepository->create($record);
        $this->findIdentifiersForQuery->add($record);
    }

    private function loadReferenceEntity(): void
    {
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );

        $this->referenceEntityRepository->create($referenceEntity);
    }
}

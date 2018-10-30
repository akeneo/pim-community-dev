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
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersForQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class ListRecordContext implements Context
{
    /** RecordItem[] */
    private $result = [];

    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /** @var FindIdentifiersForQueryInterface */
    private $findIdentifiersForQuery;

    /** @var SearchRecord */
    private $searchRecord;

    public function __construct(
        RecordRepositoryInterface $recordRepository,
        FindIdentifiersForQueryInterface $findIdentifiersForQuery,
        SearchRecord $searchRecord
    ) {
        $this->recordRepository = $recordRepository;
        $this->findIdentifiersForQuery = $findIdentifiersForQuery;
        $this->searchRecord = $searchRecord;
    }

    /**
     * @Given the records :recordCodes
    */
    public function theRecords($recordCodes)
    {
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
     * @Then the search result should be :recordCodes
    */
    public function theSearchResultShouldBe($recordCodes)
    {
        array_map(function (RecordItem $recordItem) use ($recordCodes) {
            Assert::assertContains($recordItem->code, explode(',', $recordCodes));
        }, $this->result->items);
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
        $recordCode = RecordCode::fromString($recordCode);
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $identifier = RecordIdentifier::fromString($recordCode . '_fingerprint');
        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $recordCode,
            ['en_US' => ucfirst((string) $recordCode)],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );
        $this->recordRepository->create($record);

        $this->findIdentifiersForQuery->add((string) $identifier);
    }
}

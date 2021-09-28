<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Integration\PublicApi\Platform;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Platform\SearchRecords;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Platform\SearchRecordsParameters;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SearchRecordsTest extends SqlIntegrationTestCase
{
    private SearchRecords $searchRecords;

    public function setUp(): void
    {
        parent::setUp();
        $searchRecordIndexHelper = $this->get('akeneoreference_entity.tests.helper.search_index_helper');
        $searchRecordIndexHelper->resetIndex();

        $this->searchRecords = $this->get('akeneo_referenceentity.infrastructure.persistence.query.platform.search_records');
        $this->resetDB();

        $referenceEntity = $this->createReferenceEntity('color');
        $this->createRecord($referenceEntity, 'red', ['fr_FR' => 'Rouge', 'en_US' => 'Red']);
        $this->createRecord($referenceEntity, 'black', ['fr_FR' => 'Noir', 'en_US' => 'Black']);
        $this->createRecord($referenceEntity, 'blue', ['fr_FR' => 'Bleu', 'en_US' => 'Blue']);
        $this->createRecord($referenceEntity, 'brown', ['fr_FR' => 'Brun', 'en_US' => 'Brown']);
        $this->createRecord($referenceEntity, 'white', ['fr_FR' => 'Blanc', 'en_US' => 'White']);

        $this->get('akeneo_referenceentity.client.record')->refreshIndex();
    }

    public function test_it_searches_record_codes_by_code(): void
    {
        $searchParameters = new SearchRecordsParameters();
        $searchParameters->setSearch('bl');
        $searchResult = $this->searchRecords->search('color', 'ecommerce', 'fr_FR', $searchParameters);

        self::assertEqualsCanonicalizing([
            'matches_count' => 3,
            'items' => [
                [
                    'code' => 'black',
                    'labels' => ['fr_FR' => 'Noir', 'en_US' => 'Black'],
                ],
                [
                    'code' => 'blue',
                    'labels' => ['fr_FR' => 'Bleu', 'en_US' => 'Blue'],
                ],
                [
                    'code' => 'white',
                    'labels' => ['fr_FR' => 'Blanc', 'en_US' => 'White'],
                ],
            ],
        ], $searchResult->normalize());
    }

    public function test_it_searches_record_codes_on_a_locale(): void
    {
        $searchParameters = new SearchRecordsParameters();
        $searchParameters->setSearch('no');
        $searchResult = $this->searchRecords->search('color', 'ecommerce', 'fr_FR', $searchParameters);

        self::assertEqualsCanonicalizing([
            'matches_count' => 1,
            'items' => [
                [
                    'code' => 'black',
                    'labels' => ['fr_FR' => 'Noir', 'en_US' => 'Black'],
                ],
            ],
        ], $searchResult->normalize());
    }

    public function test_it_searches_record_codes_among_an_include_codes_list(): void
    {
        $searchParameters = new SearchRecordsParameters();
        $searchParameters->setSearch('bl');
        $searchParameters->setIncludeCodes(['white', 'black']);
        $searchResult = $this->searchRecords->search('color', 'ecommerce', 'fr_FR', $searchParameters);

        self::assertEqualsCanonicalizing([
            'matches_count' => 2,
            'items' => [
                [
                    'code' => 'black',
                    'labels' => ['fr_FR' => 'Noir', 'en_US' => 'Black'],
                ],
                [
                    'code' => 'white',
                    'labels' => ['fr_FR' => 'Blanc', 'en_US' => 'White'],
                ],
            ],
        ], $searchResult->normalize());
    }

    public function test_it_searches_record_codes_and_can_exclude_codes(): void
    {
        $searchParameters = new SearchRecordsParameters();
        $searchParameters->setSearch('bl');
        $searchParameters->setExcludeCodes(['blue']);
        $searchResult = $this->searchRecords->search('color', 'ecommerce', 'fr_FR', $searchParameters);

        self::assertEqualsCanonicalizing([
            'matches_count' => 2,
            'items' => [
                [
                    'code' => 'black',
                    'labels' => ['fr_FR' => 'Noir', 'en_US' => 'Black'],
                ],
                [
                    'code' => 'white',
                    'labels' => ['fr_FR' => 'Blanc', 'en_US' => 'White'],
                ],
            ],
        ], $searchResult->normalize());
    }

    public function test_it_searches_record_codes_and_can_ignore_empty_included_codes(): void
    {
        $searchParameters = new SearchRecordsParameters();
        $searchParameters->setSearch('bl');
        $searchParameters->setExcludeCodes([]);
        $searchParameters->setIncludeCodes([]);
        $searchResult = $this->searchRecords->search('color', 'ecommerce', 'fr_FR', $searchParameters);

        self::assertEquals([
            'matches_count' => 0,
            'items' => [],
        ], $searchResult->normalize());
    }

    private function createReferenceEntity(string $referenceEntityCode): ReferenceEntity
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityCode);
        $referenceEntity = ReferenceEntity::create(
            $referenceEntityIdentifier,
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntity);

        return $referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
    }

    private function createRecord(ReferenceEntity $referenceEntity, string $recordCode, array $labels): void
    {
        /** @var RecordRepositoryInterface $recordRepository */
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $recordIdentifier = $recordRepository->nextIdentifier(
            $referenceEntity->getIdentifier(),
            RecordCode::fromString($recordCode)
        );

        $labelValues = [];
        foreach ($labels as $locale => $label) {
            $labelValues[] = Value::create(
                $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                ChannelReference::noReference(),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode($locale)),
                TextData::fromString($label)
            );
        }

        $recordRepository->create(
            Record::create(
                $recordIdentifier,
                $referenceEntity->getIdentifier(),
                RecordCode::fromString($recordCode),
                ValueCollection::fromValues($labelValues)
            )
        );

    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Integration\PublicApi\Enrich;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\SqlFindAllExistentRecordsForReferenceEntityIdentifiers;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class SqlFindAllExistentRecordForReferenceEntityIdentifiersTest extends SqlIntegrationTestCase
{
    /** @var SqlFindAllExistentRecordsForReferenceEntityIdentifiers */
    private $findAllExistentRecordForReferenceEntityIdentifiers;

    public function setUp(): void
    {
        parent::setUp();

        $this->findAllExistentRecordForReferenceEntityIdentifiers = $this->get('akeneo_referenceentity.infrastructure.persistence.query.enrich.find_all_existent_records_for_reference_entity_identifiers_public_api');
        $this->resetDB();
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    public function test_it_returns_nothing_with_empty_arguments(): void
    {
        $expected = [];
        $actual = $this->findAllExistentRecordForReferenceEntityIdentifiers->forReferenceEntityIdentifiersAndRecordCodes([]);

        Assert::assertEqualsCanonicalizing($expected, $actual);
    }

    public function test_it_returns_only_the_matching_results(): void
    {
        $this->loadDataset();

        $expected = [
            'reference_entity_1' => ['record_a', 'record_c', 'reference_entity_1_record_unique'],
            'reference_entity_2' => ['record_a', 'record_b']
        ];
        $actual = $this
            ->findAllExistentRecordForReferenceEntityIdentifiers
            ->forReferenceEntityIdentifiersAndRecordCodes(
                [
                    'reference_entity_1' => ['record_a', 'record_c', 'reference_entity_1_record_unique'],
                    'reference_entity_2' => ['record_a', 'record_b', 'a_non_existing_records']
                ]
            );

        Assert::assertEqualsCanonicalizing($expected, $actual);
    }

    private function loadDataset(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');

        $referenceEntityIdentifiers = array_map(function (int $identifier) {
            return ReferenceEntityIdentifier::fromString(sprintf('reference_entity_%d', $identifier));
        }, range(1, 4));

        foreach ($referenceEntityIdentifiers as $referenceEntityIdentifier) {
            $referenceEntityRepository->create(ReferenceEntity::create($referenceEntityIdentifier, [], Image::createEmpty()));
        }

        foreach ($referenceEntityIdentifiers as $referenceEntityIdentifier) {
            foreach (range('a', 'e') as $recordCode) {
                $recordRepository->create(
                    Record::create(
                        RecordIdentifier::fromString(sprintf('record_%s_%s', $recordCode, $referenceEntityIdentifier->normalize())),
                        $referenceEntityIdentifier,
                        RecordCode::fromString(sprintf('record_%s', $recordCode)),
                        ValueCollection::fromValues([])
                    )
                );
            }
            $recordRepository->create(
                Record::create(
                    RecordIdentifier::fromString(sprintf('toto_record_%s', $referenceEntityIdentifier->normalize())),
                    $referenceEntityIdentifier,
                    RecordCode::fromString(sprintf('%s_record_unique', $referenceEntityIdentifier->normalize())),
                    ValueCollection::fromValues([])
                )
            );
        }
    }
}

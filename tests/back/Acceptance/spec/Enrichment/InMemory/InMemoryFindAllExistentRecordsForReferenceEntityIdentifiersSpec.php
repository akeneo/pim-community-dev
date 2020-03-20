<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\AkeneoEnterprise\Test\Acceptance\Enrichment\InMemory;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\FindAllExistentRecordsForReferenceEntityIdentifiers;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryRecordRepository;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use AkeneoEnterprise\Test\Acceptance\Enrichment\InMemory\InMemoryFindAllExistentRecordsForReferenceEntityIdentifiers;
use PhpSpec\ObjectBehavior;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindAllExistentRecordsForReferenceEntityIdentifiersSpec extends ObjectBehavior
{
    function let()
    {
        $recordRepository = new InMemoryRecordRepository();

        $recordRepository->create(Record::create(
            RecordIdentifier::fromString('id1'),
            ReferenceEntityIdentifier::fromString('ref_entity1'),
            RecordCode::fromString('record1'),
            ValueCollection::fromValues([])
        ));
        $recordRepository->create(Record::create(
            RecordIdentifier::fromString('id2'),
            ReferenceEntityIdentifier::fromString('ref_entity1'),
            RecordCode::fromString('record2'),
            ValueCollection::fromValues([])
        ));
        $recordRepository->create(Record::create(
            RecordIdentifier::fromString('id3'),
            ReferenceEntityIdentifier::fromString('ref_entity2'),
            RecordCode::fromString('record3'),
            ValueCollection::fromValues([])
        ));
        $recordRepository->create(Record::create(
            RecordIdentifier::fromString('id4'),
            ReferenceEntityIdentifier::fromString('ref_entity3'),
            RecordCode::fromString('record4'),
            ValueCollection::fromValues([])
        ));

        $this->beConstructedWith($recordRepository);
    }

    function it_is_initiliazable()
    {
        $this->shouldBeAnInstanceOf(InMemoryFindAllExistentRecordsForReferenceEntityIdentifiers::class);
    }

    function it_implements_the_query_interface()
    {
        $this->shouldBeAnInstanceOf(FindAllExistentRecordsForReferenceEntityIdentifiers::class);
    }

    function it_returns_all_existent_records_for_given_identifiers()
    {
        $this->forReferenceEntityIdentifiersAndRecordCodes([
            'ref_entity1' => ['record1', 'record2', 'record3', 'record4'],
            'ref_entity2' => ['record1', 'record2', 'record3', 'record4'],
        ])
            ->shouldBe([
                'ref_entity1' => ['record1', 'record2'],
                'ref_entity2' => ['record3'],
            ]);
    }

    function it_returns_nothing_for_empty_array()
    {
        $this->forReferenceEntityIdentifiersAndRecordCodes([])->shouldBe([]);
    }
}

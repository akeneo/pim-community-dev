<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\InMemory;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindIdentifiersByReferenceEntityAndCodes;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryRecordRepository;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryReferenceEntityRepository;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class InMemoryFindIdentifiersByReferenceEntityAndCodesTest extends TestCase
{
    /** @var InMemoryRecordRepository */
    private $recordRepository;

    /** @var InMemoryReferenceEntityRepository */
    private $referenceEntityRepository;

    /** @var InMemoryFindIdentifiersByReferenceEntityAndCodes */
    private $query;

    /** @var ReferenceEntityIdentifier */
    private $starckIdentifier;

    /** @var ReferenceEntityIdentifier */
    private $cocoIdentifier;

    public function setUp(): void
    {
        $this->recordRepository = new InMemoryRecordRepository();
        $this->referenceEntityRepository = new InMemoryReferenceEntityRepository(new EventDispatcher());
        $this->query = new InMemoryFindIdentifiersByReferenceEntityAndCodes(
            $this->recordRepository
        );
    }

    /**
     * @test
     */
    public function it_finds_identifiers_of_records_by_their_reference_entity_and_codes()
    {
        $this->loadFixtures();

        $identifiers = $this->query->find(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                RecordCode::fromString('starck'),
                RecordCode::fromString('coco'),
            ]
        );

        $this->assertCount(2, $identifiers);
        $this->assertContainsEquals($this->starckIdentifier->normalize(), $identifiers);
        $this->assertContainsEquals($this->cocoIdentifier->normalize(), $identifiers);

        $identifiers = $this->query->find(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                RecordCode::fromString('coco'),
            ]
        );

        $this->assertCount(1, $identifiers);
        $this->assertContainsEquals($this->cocoIdentifier->normalize(), $identifiers);
        $this->assertNotContainsEquals($this->starckIdentifier->normalize(), $identifiers);
    }

    private function loadFixtures()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntity = ReferenceEntity::create(
            $referenceEntityIdentifier,
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $this->referenceEntityRepository->create($referenceEntity);

        $starkCode = RecordCode::fromString('starck');
        $this->starckIdentifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $starkCode);
        $this->recordRepository->create(
            Record::create(
                $this->starckIdentifier,
                $referenceEntityIdentifier,
                $starkCode,
                ValueCollection::fromValues([])
            )
        );

        $cocoCode = RecordCode::fromString('coco');
        $this->cocoIdentifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $cocoCode);
        $this->recordRepository->create(
            Record::create(
                $this->cocoIdentifier,
                $referenceEntityIdentifier,
                $cocoCode,
                ValueCollection::fromValues([])
            )
        );
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Record;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersByReferenceEntityAndCodesInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlFindIdentifiersByReferenceEntityAndCodesTest extends SqlIntegrationTestCase
{
    /** @var FindIdentifiersByReferenceEntityAndCodesInterface */
    private $findIdentifiersByReferenceEntityAndCodes;

    /** @var RecordIdentifier */
    private $starckIdentifier;

    /** @var RecordIdentifier */
    private $cocoIdentifier;

    public function setup()
    {
        parent::setUp();

        $this->findIdentifiersByReferenceEntityAndCodes = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_identifiers_by_reference_entity_and_codes');
        $this->resetDB();
        $this->loadReferenceEntityAndRecords();
    }

    /**
     * @test
     */
    public function it_finds_identifiers_of_records_by_their_reference_entity_and_codes()
    {
        $identifiers = $this->findIdentifiersByReferenceEntityAndCodes->find(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                RecordCode::fromString('starck'),
                RecordCode::fromString('coco'),
            ]
        );

        $this->assertCount(2, $identifiers);
        $this->assertContains($this->starckIdentifier->normalize(), $identifiers);
        $this->assertContains($this->cocoIdentifier->normalize(), $identifiers);

        $identifiers = $this->findIdentifiersByReferenceEntityAndCodes->find(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                RecordCode::fromString('coco'),
            ]
        );

        $this->assertCount(1, $identifiers);
        $this->assertContains($this->cocoIdentifier->normalize(), $identifiers);
        $this->assertNotContains($this->starckIdentifier->normalize(), $identifiers);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadReferenceEntityAndRecords(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntity = ReferenceEntity::create(
            $referenceEntityIdentifier,
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntity);

        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $starkCode = RecordCode::fromString('starck');
        $this->starckIdentifier = $recordRepository->nextIdentifier($referenceEntityIdentifier, $starkCode);
        $recordRepository->create(
            Record::create(
                $this->starckIdentifier,
                $referenceEntityIdentifier,
                $starkCode,
                ValueCollection::fromValues([])
            )
        );

        $cocoCode = RecordCode::fromString('coco');
        $this->cocoIdentifier = $recordRepository->nextIdentifier($referenceEntityIdentifier, $cocoCode);
        $recordRepository->create(
            Record::create(
                $this->cocoIdentifier,
                $referenceEntityIdentifier,
                $cocoCode,
                ValueCollection::fromValues([])
            )
        );
    }
}

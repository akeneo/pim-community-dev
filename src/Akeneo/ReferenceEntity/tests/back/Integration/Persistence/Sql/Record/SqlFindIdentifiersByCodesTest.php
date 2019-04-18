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
use Akeneo\ReferenceEntity\Domain\Query\Record\FindCodesByIdentifiersInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersByCodesInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlFindIdentifiersByCodesTest extends SqlIntegrationTestCase
{
    /** @var FindIdentifiersByCodesInterface */
    private $query;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_identifiers_by_codes');
        $this->resetDB();
        $this->loadReferenceEntityDesigner();
        $this->loadRecords();
    }

    /**
     * @test
     */
    public function it_finds_record_codes_given_their_identifiers()
    {
        $codes = $this->query->find('designer', ['starck', 'jacobs']);

        $this->assertEquals(['designer_jacobs_fingerprint', 'designer_stark_fingerprint'], $codes);

        $codes = $this->query->find('designer', ['unknown', 'jacobs']);

        $this->assertEquals(['designer_jacobs_fingerprint'], $codes);

        $codes = $this->query->find('designer', ['unknown']);

        $this->assertEmpty($codes);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadReferenceEntityDesigner(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntity);
    }

    public function loadRecords(): void
    {
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $designerIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $starkCode = RecordCode::fromString('starck');
        $starkIdentifier = RecordIdentifier::create('designer', 'stark', 'fingerprint');
        $recordRepository->create(
            Record::create(
                $starkIdentifier,
                $designerIdentifier,
                $starkCode,
                ValueCollection::fromValues([])
            )
        );

        $jacobsCode = RecordCode::fromString('jacobs');
        $jacobsIdentifier = RecordIdentifier::create('designer', 'jacobs', 'fingerprint');
        $recordRepository->create(
            Record::create(
                $jacobsIdentifier,
                $designerIdentifier,
                $jacobsCode,
                ValueCollection::fromValues([])
            )
        );
    }
}

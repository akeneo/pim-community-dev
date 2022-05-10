<?php

namespace Akeneo\Pim\TableAttribute\tests\back\Enterprise\Integration\Value\Query;

use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\LabelCollection;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\Query\GetExistingRecordCodes;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\Query\SqlGetExistingRecordCodes;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class SqlGetExistingRecordCodesIntegration extends TestCase
{
    private SqlGetExistingRecordCodes $sqlGetExistingRecordCodes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_referenceentity.infrastructure.persistence.query.channel.find_channels')
            ->setChannels([
                new Channel('ecommerce', ['en_US'], LabelCollection::fromArray(['en_US' => 'Ecommerce', 'de_DE' => 'Ecommerce', 'fr_FR' => 'Ecommerce']), ['USD'])
            ]);

        $this->sqlGetExistingRecordCodes = $this->get(GetExistingRecordCodes::class);

        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            ['en_US' => 'Brand'],
            Image::createEmpty()
        );

        $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity')->create($referenceEntity);

        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $recordCode1 = RecordCode::fromString('Ferrari');
        $record1 = Record::create(
            $recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode1),
            $referenceEntityIdentifier,
            $recordCode1,
            ValueCollection::fromValues([])
        );

        $recordRepository->create($record1);

        $recordCode2 = RecordCode::fromString('Dacia');
        $record2 = Record::create(
            $recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode2),
            $referenceEntityIdentifier,
            $recordCode2,
            ValueCollection::fromValues([])
        );

        $recordRepository->create($record2);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_reference_entity_does_not_exist(): void
    {
        $referenceEntityUnknown = ReferenceEntityIdentifier::fromString('unknown');
        $result = $this->sqlGetExistingRecordCodes->fromReferenceEntityIdentifierAndRecordCodes(
            [$referenceEntityUnknown->__toString() => ['toto', 'titi']]
        );
        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function it_returns_existing_reference_entity_codes(): void
    {
        $referenceEntityBrand = ReferenceEntityIdentifier::fromString('brand');
        $result = $this->sqlGetExistingRecordCodes->fromReferenceEntityIdentifierAndRecordCodes(
            [$referenceEntityBrand->__toString() => ['Ferrari', 'Dacia', 'unknown']]
        );

        self::assertEqualsCanonicalizing([$referenceEntityBrand->__toString() => ['Ferrari', 'Dacia']], $result);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_no_record_exists(): void
    {
        $referenceEntityBrand = ReferenceEntityIdentifier::fromString('brand');
        $result = $this->sqlGetExistingRecordCodes->fromReferenceEntityIdentifierAndRecordCodes(
            [$referenceEntityBrand->__toString() => ['unknown']]
        );
        self::assertSame([], $result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}

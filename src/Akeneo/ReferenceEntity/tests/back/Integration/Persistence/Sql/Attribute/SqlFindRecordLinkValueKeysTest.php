<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\SqlFindRecordLinkValueKeys;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlFindRecordLinkValueKeysTest extends SqlIntegrationTestCase
{
    /** @var SqlFindRecordLinkValueKeys */
    private $query;

    public function setUp()
    {
        parent::setUp();

        $this->query = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_record_link_value_keys');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_there_is_no_such_value_key()
    {
        $this->loadReferenceEntities();

        $valueKeys = $this->query->fetch(ReferenceEntityIdentifier::fromString('designer'));
        $this->assertEmpty($valueKeys);
    }

    /**
     * @test
     */
    public function it_returns_value_keys_with_metadata_of_attribute()
    {
        $this->loadReferenceEntities();
        $this->loadRecordLinkAttributes();

        $expected = [
            [
                'value_key' => 'cities_abcdef123456789_de_DE',
                'attribute_identifier' => 'cities_abcdef123456789',
                'record_type' => 'city',
                'attribute_type' => 'record_collection',
            ],
            [
                'value_key' => 'cities_abcdef123456789_en_US',
                'attribute_identifier' => 'cities_abcdef123456789',
                'record_type' => 'city',
                'attribute_type' => 'record_collection',
            ],
            [
                'value_key' => 'cities_abcdef123456789_fr_FR',
                'attribute_identifier' => 'cities_abcdef123456789',
                'record_type' => 'city',
                'attribute_type' => 'record_collection',
            ],
            [
                'value_key' => 'main_city_abcdef123456789',
                'attribute_identifier' => 'main_city_abcdef123456789',
                'record_type' => 'city',
                'attribute_type' => 'record',
            ],
        ];

        $valueKeys = $this->query->fetch(ReferenceEntityIdentifier::fromString('designer'));
        $this->assertSame($expected, $valueKeys);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadReferenceEntities(): void
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

        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('city'),
            [
                'fr_FR' => 'Ville',
                'en_US' => 'City',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntity);
    }

    private function loadRecordLinkAttributes(): void
    {
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');

        $recordAttribute = RecordAttribute::create(
            AttributeIdentifier::fromString('main_city_abcdef123456789'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('main_city'),
            LabelCollection::fromArray(['en_US' => 'Main City']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString('city')
        );
        $attributeRepository->create($recordAttribute);

        $recordCollectionAttribute = RecordCollectionAttribute::create(
            AttributeIdentifier::fromString('cities_abcdef123456789'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('cities'),
            LabelCollection::fromArray(['en_US' => 'Cities']),
            AttributeOrder::fromInteger(6),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            ReferenceEntityIdentifier::fromString('city')
        );
        $attributeRepository->create($recordCollectionAttribute);
    }
}

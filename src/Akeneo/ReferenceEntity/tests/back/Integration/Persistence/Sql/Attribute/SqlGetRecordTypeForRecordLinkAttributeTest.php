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

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\SqlFindValueKeyCollectionForAttributeType;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\SqlGetRecordTypeForRecordLinkAttribute;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlGetRecordTypeForRecordLinkAttributeTest extends SqlIntegrationTestCase
{
    /** @var SqlGetRecordTypeForRecordLinkAttribute */
    private $getRecordTypeForRecordLinkAttribute;

    /** @var ReferenceEntityIdentifier */
    private $referenceEntityIdentifier;

    public function setUp()
    {
        parent::setUp();

        $this->getRecordTypeForRecordLinkAttribute = $this->get('akeneo_referenceentity.infrastructure.persistence.query.get_record_type_for_record_link_attribute');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_throws_if_the_given_attribute_identifier_does_not_exists()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->getRecordTypeForRecordLinkAttribute->fetch('unknown_attribute_identifier');
    }

    /**
     * @test
     */
    public function it_throws_if_the_given_attribute_identifier_is_not_of_type_record_link()
    {
        $this->loadReferenceEntity();
        $this->loadSimpleOptionAttribute('attribute_with_wrong_type');
        $this->expectException(\InvalidArgumentException::class);
        $this->getRecordTypeForRecordLinkAttribute->fetch('unknown_attribute_identifier');
    }

    /**
     * @test
     */
    public function it_returns_the_reference_entity_type_for_simple_record_attribute()
    {
        $this->loadReferenceEntity();
        $this->loadRecordAttribute('simple_record_link_attribute', 'designer');

        $recordType = $this->getRecordTypeForRecordLinkAttribute->fetch('simple_record_link_attribute');

        $this->assertEquals('designer', $recordType);
    }

    /**
     * @test
     */
    public function it_returns_the_reference_entity_type_for_the_record_collection_attribute()
    {
        $this->loadReferenceEntity();
        $this->loadRecordCollectionAttribute('multiple_record_link_attribute', 'designer');

        $recordType = $this->getRecordTypeForRecordLinkAttribute->fetch('multiple_record_link_attribute');

        $this->assertEquals('designer', $recordType);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadReferenceEntity(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $this->referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntity = ReferenceEntity::create(
            $this->referenceEntityIdentifier,
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntity);
    }

    private function loadRecordAttribute(string $attributeIdentifier, string $referenceEntityType): void
    {
        $recordAttribute = RecordAttribute::create(
            AttributeIdentifier::fromString($attributeIdentifier),
            $this->referenceEntityIdentifier,
            AttributeCode::fromString('mentor'),
            LabelCollection::fromArray(['en_US' => 'Mentor']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString($referenceEntityType)
        );
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attributeRepository->create($recordAttribute);
    }

    private function loadRecordCollectionAttribute(string $attributeIdentifier, string $referenceEntityType): void
    {
        $recordAttribute = RecordCollectionAttribute::create(
            AttributeIdentifier::fromString($attributeIdentifier),
            $this->referenceEntityIdentifier,
            AttributeCode::fromString('mentor'),
            LabelCollection::fromArray(['en_US' => 'Mentor']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString($referenceEntityType)
        );
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attributeRepository->create($recordAttribute);
    }

    private function loadSimpleOptionAttribute(string $attributeIdentifier): void
    {
        $optionAttribute = OptionAttribute::create(
            AttributeIdentifier::fromString($attributeIdentifier),
            $this->referenceEntityIdentifier,
            AttributeCode::fromString('favorite_color'),
            LabelCollection::fromArray(['en_US' => 'Favorite color']),
            AttributeOrder::fromInteger(6),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attributeRepository->create($optionAttribute);
    }
}

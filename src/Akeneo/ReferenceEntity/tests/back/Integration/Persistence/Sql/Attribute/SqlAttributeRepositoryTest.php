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

use Akeneo\ReferenceEntity\Common\Fake\EventDispatcherMock;
use Akeneo\ReferenceEntity\Domain\Event\AttributeDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Event\AttributeOptionsDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\DBALException;

class SqlAttributeRepositoryTest extends SqlIntegrationTestCase
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var EventDispatcherMock */
    private $eventDispatcherMock;

    public function setUp()
    {
        parent::setUp();

        $this->attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $this->eventDispatcherMock = $this->get('event_dispatcher');
        $this->eventDispatcherMock->reset();

        $this->resetDB();
        $this->insertReferenceEntity();
    }

    /**
     * @test
     */
    public function it_creates_an_attribute_of_type_text_and_returns_it()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('name');
        $identifier = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($referenceEntityIdentifier, $attributeCode);

        $expectedAttribute = TextAttribute::createTextarea(
            $identifier,
            $referenceEntityIdentifier,
            $attributeCode,
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(255),
            AttributeIsRichTextEditor::fromBoolean(false)
        );

        $this->attributeRepository->create($expectedAttribute);

        $actualAttribute = $this->attributeRepository->getByIdentifier($identifier);
        $this->assertAttribute($expectedAttribute, $actualAttribute);
    }

    /**
     * @test
     */
    public function it_creates_an_attribute_of_type_image_and_returns_it()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('picture');
        $identifier = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($referenceEntityIdentifier, $attributeCode);

        $expectedAttribute = ImageAttribute::create(
            $identifier,
            $referenceEntityIdentifier,
            AttributeCode::fromString('picture'),
            LabelCollection::fromArray(['en_US' => 'Picture', 'fr_FR' => 'Image']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('250.12'),
            AttributeAllowedExtensions::fromList(['pdf', 'png'])
        );

        $this->attributeRepository->create($expectedAttribute);

        $actualAttribute = $this->attributeRepository->getByIdentifier($identifier);
        $this->assertAttribute($expectedAttribute, $actualAttribute);
    }

    /**
     * @test
     */
    public function it_creates_an_attribute_of_type_record_and_returns_it()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('mentor');
        $identifier = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($referenceEntityIdentifier, $attributeCode);

        $expectedAttribute = RecordAttribute::create(
            $identifier,
            $referenceEntityIdentifier,
            AttributeCode::fromString('mentor'),
            LabelCollection::fromArray(['en_US' => 'Mentor', 'fr_FR' => 'Mentor']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            $referenceEntityIdentifier
        );

        $this->attributeRepository->create($expectedAttribute);

        $actualAttribute = $this->attributeRepository->getByIdentifier($identifier);
        $this->assertAttribute($expectedAttribute, $actualAttribute);
    }

    /**
     * @test
     */
    public function it_creates_an_attribute_of_type_record_collection_and_returns_it()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('brands');
        $identifier = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($referenceEntityIdentifier, $attributeCode);

        $expectedAttribute = RecordCollectionAttribute::create(
            $identifier,
            $referenceEntityIdentifier,
            AttributeCode::fromString('brands'),
            LabelCollection::fromArray(['en_US' => 'Brands', 'fr_FR' => 'Marques']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString('brand')
        );

        $this->attributeRepository->create($expectedAttribute);

        $actualAttribute = $this->attributeRepository->getByIdentifier($identifier);
        $this->assertAttribute($expectedAttribute, $actualAttribute);
    }

    /**
     * @test
     */
    public function it_creates_an_attribute_of_type_option_and_returns_it()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('favorite_color');
        $identifier = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($referenceEntityIdentifier, $attributeCode);

        $expectedOption = OptionAttribute::create(
            $identifier,
            $referenceEntityIdentifier,
            $attributeCode,
            LabelCollection::fromArray(['en_US' => 'Favorite Color', 'fr_FR' => 'Couleur favorite']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );
        $expectedOption->setOptions([
            AttributeOption::create(OptionCode::fromString('red'), LabelCollection::fromArray(['en_US' => 'Red'])),
            AttributeOption::create(OptionCode::fromString('green'), LabelCollection::fromArray(['en_US' => 'Green']))
        ]);

        $this->attributeRepository->create($expectedOption);

        $actualOption = $this->attributeRepository->getByIdentifier($identifier);
        $this->assertAttribute($expectedOption, $actualOption);
    }

    /**
     * @test
     */
    public function it_creates_an_attribute_of_type_option_collection_and_returns_it()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('colors');
        $identifier = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($referenceEntityIdentifier, $attributeCode);

        $expectedOption = OptionCollectionAttribute::create(
            $identifier,
            $referenceEntityIdentifier,
            AttributeCode::fromString('colors'),
            LabelCollection::fromArray(['en_US' => 'Colors', 'fr_FR' => 'Couleurs']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );
        $expectedOption->setOptions([
            AttributeOption::create(OptionCode::fromString('red'), LabelCollection::fromArray(['en_US' => 'Red'])),
            AttributeOption::create(OptionCode::fromString('green'), LabelCollection::fromArray(['en_US' => 'Green']))
        ]);

        $this->attributeRepository->create($expectedOption);

        $actualOption = $this->attributeRepository->getByIdentifier($identifier);
        $this->assertAttribute($expectedOption, $actualOption);
    }


    /**
     * @test
     */
    public function it_throws_when_creating_an_attribute_with_the_same_identifier()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('name');
        $identifier = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($referenceEntityIdentifier, $attributeCode);

        $attribute = $this->createAttributeWithIdentifier($identifier);
        $this->attributeRepository->create($attribute);

        $this->expectException(DBALException::class);
        $this->attributeRepository->create($attribute);
    }

    /**
     * @test
     */
    public function it_throws_if_the_identifier_is_not_found()
    {
        $identifier = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute')
            ->nextIdentifier(ReferenceEntityIdentifier::fromString('designer'), AttributeCode::fromString('bio'));

        $this->expectException(AttributeNotFoundException::class);
        $this->attributeRepository->getByIdentifier($identifier);
    }

    /**
     * @test
     */
    public function it_throws_if_the_attribute_type_is_not_known()
    {
        $identifier = $this->insertRowWithUnsupportedType();
        $this->expectException(\RuntimeException::class);
        $this->attributeRepository->getByIdentifier($identifier);
    }

    /**
     * @test
     */
    public function it_deletes_an_attribute_by_its_identifier()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('name');
        $identifier = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($referenceEntityIdentifier, $attributeCode);

        $textAttribute = $this->createAttributeWithIdentifier($identifier);
        $this->attributeRepository->create($textAttribute);

        $this->attributeRepository->deleteByIdentifier($identifier);

        $this->eventDispatcherMock->assertEventDispatched(AttributeDeletedEvent::class);
        $this->expectException(AttributeNotFoundException::class);
        $this->attributeRepository->getByIdentifier($identifier);
    }

    /** @test */
    public function it_updates_a_text_area_attribute()
    {
        $identifier = AttributeIdentifier::create('designer', 'name', 'test');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $expectedAttribute = TextAttribute::createTextarea(
            $identifier,
            $referenceEntityIdentifier,
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Description', 'fr_FR' => 'Description']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(255),
            AttributeIsRichTextEditor::fromBoolean(false)
        );
        $this->attributeRepository->create($expectedAttribute);

        $expectedAttribute->updateLabels(LabelCollection::fromArray(['fr_FR' => 'Biography', 'en_US' => 'Biographie']));
        $expectedAttribute->setMaxLength(AttributeMaxLength::fromInteger(100));
        $expectedAttribute->setIsRichTextEditor(AttributeIsRichTextEditor::fromBoolean(true));
        $this->attributeRepository->update($expectedAttribute);

        $actualAttribute = $this->attributeRepository->getByIdentifier($identifier);
        $this->assertAttribute($expectedAttribute, $actualAttribute);
    }

    /**
     * @test
     */
    public function it_updates_a_text_attribute()
    {
        $identifier = AttributeIdentifier::create('designer', 'name', 'test');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $expectedAttribute = TextAttribute::createText(
            $identifier,
            $referenceEntityIdentifier,
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(255),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->attributeRepository->create($expectedAttribute);

        $expectedAttribute->updateLabels(LabelCollection::fromArray(['fr_FR' => 'Surnom', 'en_US' => 'Nickname']));
        $expectedAttribute->setMaxLength(AttributeMaxLength::fromInteger(100));
        $expectedAttribute->setValidationRule(AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION));
        $expectedAttribute->setRegularExpression(AttributeRegularExpression::fromString('/[0-9]+/'));
        $this->attributeRepository->update($expectedAttribute);

        $actualAttribute = $this->attributeRepository->getByIdentifier($identifier);
        $this->assertAttribute($expectedAttribute, $actualAttribute);
    }

    /**
     * @test
     */
    public function it_counts_all_reference_entity_attributes()
    {
        $designerIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $this->assertEquals(2, $this->attributeRepository->countByReferenceEntity($designerIdentifier));

        $identifier = AttributeIdentifier::create('designer', 'name', 'test');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $expectedAttribute = TextAttribute::createText(
            $identifier,
            $referenceEntityIdentifier,
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(255),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->attributeRepository->create($expectedAttribute);

        $this->assertEquals(3, $this->attributeRepository->countByReferenceEntity($designerIdentifier));

        $identifier = AttributeIdentifier::create('designer', 'name2', 'test');
        $expectedAttribute = TextAttribute::createText(
            $identifier,
            $referenceEntityIdentifier,
            AttributeCode::fromString('name2'),
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(255),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->attributeRepository->create($expectedAttribute);

        $this->assertEquals(4, $this->attributeRepository->countByReferenceEntity($designerIdentifier));
    }

    private function assertAttribute(
        AbstractAttribute $expectedAttribute,
        AbstractAttribute $actualAttribute
    ): void {
        $expected = $expectedAttribute->normalize();
        $actual = $actualAttribute->normalize();
        sort($expected['labels']);
        sort($actual['labels']);
        $this->assertEquals($expected, $actual);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function insertReferenceEntity(): void
    {
        $repository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer'
            ],
            Image::createEmpty()
        );
        $repository->create($referenceEntity);
    }

    private function insertRowWithUnsupportedType(): AttributeIdentifier
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('age');
        $identifier = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($referenceEntityIdentifier, $attributeCode);

        $sqlConnection = $this->get('database_connection');
        $query = <<<SQL
        INSERT INTO akeneo_reference_entity_attribute (
            identifier,
            code,
            reference_entity_identifier,
            labels,
            attribute_type,
            attribute_order,
            is_required,
            value_per_channel,
            value_per_locale,
            additional_properties
        )
        VALUES (
            :identifier,
            :code,
            :reference_entity_identifier,
            :labels,
            :attribute_type,
            :attribute_order,
            :is_required,
            :value_per_channel,
            :value_per_locale,
            :additional_properties
        );
SQL;
        $assertRows = $sqlConnection->executeUpdate(
            $query,
            [
                'identifier'                 => (string) $identifier,
                'code'                       => (string) $attributeCode,
                'reference_entity_identifier' => (string) $referenceEntityIdentifier,
                'labels'                     => '{}',
                'attribute_type'             => 'UNSUPPORTED_ATTRIBUTE_TYPE',
                'attribute_order'            => 2,
                'is_required'                   => 0,
                'value_per_channel'          => 0,
                'value_per_locale'           => 0,
                'additional_properties'      => '{}',
            ]
        );

        $this->assertEquals(1, $assertRows);

        return $identifier;
    }

    private function createAttributeWithIdentifier(AttributeIdentifier $identifier): AbstractAttribute
    {
        return TextAttribute::createText(
            $identifier,
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
    }
}

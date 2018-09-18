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

namespace Akeneo\EnrichedEntity\Integration\Persistence\Sql\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\EnrichedEntity\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\DBALException;

class SqlAttributeRepositoryTest extends SqlIntegrationTestCase
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function setUp()
    {
        parent::setUp();

        $this->attributeRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.attribute');
        $this->resetDB();
        $this->insertEnrichedEntity();
    }

    /**
     * @test
     */
    public function it_creates_an_attribute_of_type_text_and_returns_it()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('name');
        $identifier = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($enrichedEntityIdentifier, $attributeCode);

        $expectedAttribute = TextAttribute::createTextarea(
            $identifier,
            $enrichedEntityIdentifier,
            $attributeCode,
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(0),
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
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('picture');
        $identifier = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($enrichedEntityIdentifier, $attributeCode);

        $expectedAttribute = ImageAttribute::create(
            $identifier,
            $enrichedEntityIdentifier,
            AttributeCode::fromString('picture'),
            LabelCollection::fromArray(['en_US' => 'Picture', 'fr_FR' => 'Image']),
            AttributeOrder::fromInteger(0),
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
    public function it_throws_when_creating_an_attribute_with_the_same_identifier()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('name');
        $identifier = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($enrichedEntityIdentifier, $attributeCode);

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
        $identifier = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.attribute')
            ->nextIdentifier(EnrichedEntityIdentifier::fromString('designer'), AttributeCode::fromString('bio'));

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
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('name');
        $identifier = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($enrichedEntityIdentifier, $attributeCode);

        $textAttribute = $this->createAttributeWithIdentifier($identifier);
        $this->attributeRepository->create($textAttribute);

        $this->attributeRepository->deleteByIdentifier($identifier);

        $this->expectException(AttributeNotFoundException::class);
        $this->attributeRepository->getByIdentifier($identifier);
    }

    /** @test */
    public function it_updates_a_text_area_attribute()
    {
        $identifier = AttributeIdentifier::create('designer', 'name', 'test');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $expectedAttribute = TextAttribute::createTextarea(
            $identifier,
            $enrichedEntityIdentifier,
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Description', 'fr_FR' => 'Description']),
            AttributeOrder::fromInteger(0),
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
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $expectedAttribute = TextAttribute::createText(
            $identifier,
            $enrichedEntityIdentifier,
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(0),
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

    private function assertAttribute(
        AbstractAttribute $expectedAttribute,
        AbstractAttribute $actualAttribute
    ): void {
        $expected = $expectedAttribute->normalize();
        $actual = $actualAttribute->normalize();
        sort($expected['labels']);
        sort($actual['labels']);
        $this->assertSame($expected, $actual);
    }

    private function resetDB(): void
    {
        $this->get('akeneoenriched_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function insertEnrichedEntity(): void
    {
        $repository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.enriched_entity');
        $enrichedEntity = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer'
            ],
            null
        );
        $repository->create($enrichedEntity);
    }

    private function insertRowWithUnsupportedType(): AttributeIdentifier
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('age');
        $identifier = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($enrichedEntityIdentifier, $attributeCode);

        $sqlConnection = $this->get('database_connection');
        $query = <<<SQL
        INSERT INTO akeneo_enriched_entity_attribute (
            identifier,
            code,
            enriched_entity_identifier,
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
            :enriched_entity_identifier,
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
                'enriched_entity_identifier' => (string) $enrichedEntityIdentifier,
                'labels'                     => '{}',
                'attribute_type'             => 'UNSUPPORTED_ATTRIBUTE_TYPE',
                'attribute_order'            => 1,
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
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
    }
}

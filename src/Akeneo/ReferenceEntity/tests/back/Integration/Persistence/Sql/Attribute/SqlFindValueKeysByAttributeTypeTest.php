<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlFindValueKeysByAttributeTypeTest extends SqlIntegrationTestCase
{
    /** @var FindValueKeysByAttributeTypeInterface */
    private $findValueKeysByAttributeType;

    private $order = 2;

    public function setUp(): void
    {
        parent::setUp();

        $this->findValueKeysByAttributeType = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_value_keys_by_attribute_type');
        $this->resetDB();
        $this->loadReferenceEntity();
    }

    /**
     * @test
     */
    public function it_returns_all_value_keys_of_given_attribute_types()
    {
        $designer = ReferenceEntityIdentifier::fromString('designer');
        $identifiers = $this->loadAttributes();

        $referenceEntity = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity')
            ->getByIdentifier($designer);
        $attributeAsLabelIdentifier = $referenceEntity->getAttributeAsLabelReference()->getIdentifier();

        $textValueKeys = $this->findValueKeysByAttributeType->find($designer, ['text']);
        $this->assertCount(7, $textValueKeys);
        $this->assertContains(sprintf('%s_de_DE', $attributeAsLabelIdentifier), $textValueKeys);
        $this->assertContains(sprintf('%s_en_US', $attributeAsLabelIdentifier), $textValueKeys);
        $this->assertContains(sprintf('%s_fr_FR', $attributeAsLabelIdentifier), $textValueKeys);
        $this->assertContains(sprintf('%s_ecommerce_en_US', $identifiers['text']), $textValueKeys);
        $this->assertContains(sprintf('%s_ecommerce_fr_FR', $identifiers['text']), $textValueKeys);
        $this->assertContains(sprintf('%s_mobile_de_DE', $identifiers['text']), $textValueKeys);
        $this->assertContains(sprintf('%s_print_en_US', $identifiers['text']), $textValueKeys);

        $optionValueKeys = $this->findValueKeysByAttributeType->find($designer, ['option']);
        $this->assertCount(4, $optionValueKeys);
        $this->assertContains(sprintf('%s_ecommerce_en_US', $identifiers['option']), $optionValueKeys);
        $this->assertContains(sprintf('%s_ecommerce_fr_FR', $identifiers['option']), $optionValueKeys);
        $this->assertContains(sprintf('%s_mobile_de_DE', $identifiers['option']), $optionValueKeys);
        $this->assertContains(sprintf('%s_print_en_US', $identifiers['option']), $optionValueKeys);

        $recordCollectionValueKeys = $this->findValueKeysByAttributeType->find($designer, ['record_collection']);
        $this->assertCount(4, $recordCollectionValueKeys);
        $this->assertContains(sprintf('%s_ecommerce_en_US', $identifiers['record_collection']), $recordCollectionValueKeys);
        $this->assertContains(sprintf('%s_ecommerce_fr_FR', $identifiers['record_collection']), $recordCollectionValueKeys);
        $this->assertContains(sprintf('%s_mobile_de_DE', $identifiers['record_collection']), $recordCollectionValueKeys);
        $this->assertContains(sprintf('%s_print_en_US', $identifiers['record_collection']), $recordCollectionValueKeys);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadReferenceEntity(): void
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

    private function loadAttributes(): array
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $identifiers = [];

        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');

        $attrCode = AttributeCode::fromString('text_attr');
        $identifier = $attributeRepository->nextIdentifier($referenceEntityIdentifier, $attrCode);
        $identifiers['text'] = $identifier;
        $textAttribute = TextAttribute::createText(
            $identifier,
            $referenceEntityIdentifier,
            $attrCode,
            LabelCollection::fromArray(['fr_FR' => 'dummy label']),
            AttributeOrder::fromInteger($this->order++),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(25),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $attributeRepository->create($textAttribute);

        $attrCode = AttributeCode::fromString('single_option_attr');
        $identifier = $attributeRepository->nextIdentifier($referenceEntityIdentifier, $attrCode);
        $identifiers['option'] = $identifier;
        $optionAttribute = OptionAttribute::create(
            $identifier,
            $referenceEntityIdentifier,
            $attrCode,
            LabelCollection::fromArray(['fr_FR' => 'dummy label']),
            AttributeOrder::fromInteger($this->order++),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true)
        );
        $attributeRepository->create($optionAttribute);

        $attrCode = AttributeCode::fromString('record_collection_attr');
        $identifier = $attributeRepository->nextIdentifier($referenceEntityIdentifier, $attrCode);
        $identifiers['record_collection'] = $identifier;
        $recordCollAttribute = RecordCollectionAttribute::create(
            $identifier,
            $referenceEntityIdentifier,
            $attrCode,
            LabelCollection::fromArray(['fr_FR' => 'dummy label']),
            AttributeOrder::fromInteger($this->order++),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            ReferenceEntityIdentifier::fromString('designer')
        );
        $attributeRepository->create($recordCollAttribute);

        return $identifiers;
    }
}

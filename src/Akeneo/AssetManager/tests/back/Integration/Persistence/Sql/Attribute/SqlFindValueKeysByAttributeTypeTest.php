<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Persistence\Sql\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlFindValueKeysByAttributeTypeTest extends SqlIntegrationTestCase
{
    private FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType;

    private int $order = 2;

    public function setUp(): void
    {
        parent::setUp();

        $this->findValueKeysByAttributeType = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_value_keys_by_attribute_type');
        $this->resetDB();
        $this->loadAssetFamily();
    }

    /**
     * @test
     */
    public function it_returns_all_value_keys_of_given_attribute_types()
    {
        $designer = AssetFamilyIdentifier::fromString('designer');
        $identifiers = $this->loadAttributes();

        $assetFamily = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family')
            ->getByIdentifier($designer);
        $attributeAsLabelIdentifier = $assetFamily->getAttributeAsLabelReference()->getIdentifier();

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
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadAssetFamily(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository->create($assetFamily);
    }

    private function loadAttributes(): array
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $identifiers = [];

        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');

        $attrCode = AttributeCode::fromString('text_attr');
        $identifier = $attributeRepository->nextIdentifier($assetFamilyIdentifier, $attrCode);
        $identifiers['text'] = $identifier;
        $textAttribute = TextAttribute::createText(
            $identifier,
            $assetFamilyIdentifier,
            $attrCode,
            LabelCollection::fromArray(['fr_FR' => 'dummy label']),
            AttributeOrder::fromInteger($this->order++),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(25),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $attributeRepository->create($textAttribute);

        $attrCode = AttributeCode::fromString('single_option_attr');
        $identifier = $attributeRepository->nextIdentifier($assetFamilyIdentifier, $attrCode);
        $identifiers['option'] = $identifier;
        $optionAttribute = OptionAttribute::create(
            $identifier,
            $assetFamilyIdentifier,
            $attrCode,
            LabelCollection::fromArray(['fr_FR' => 'dummy label']),
            AttributeOrder::fromInteger($this->order++),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true)
        );
        $attributeRepository->create($optionAttribute);

        return $identifiers;
    }
}

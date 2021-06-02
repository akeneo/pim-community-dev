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

namespace Akeneo\AssetManager\Integration\Persistence\Sql\ValueKey;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\ValueKey\SqlGetValueKeyForAttributeChannelAndLocale;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlGetValueKeyForAttributeChannelAndLocaleTest extends SqlIntegrationTestCase
{
    private SqlGetValueKeyForAttributeChannelAndLocale $getValueKeyForAttributeChannelAndLocale;

    private AttributeRepositoryInterface $attributeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->getValueKeyForAttributeChannelAndLocale = $this->get('akeneo_assetmanager.infrastructure.persistence.query.get_value_key_for_attribute_channel_and_locale');
        $this->attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $this->resetDB();
        $this->loadAssetFamilies();
    }

    /**
     * @test
     */
    public function it_returns_the_value_key_for_attributes_without_channel_nor_locale()
    {
        $this->loadAttributeWithoutValuePerChannelOrLocale('attribute_without_channel_nor_locale');
        $valueKey = $this->getValueKeyForAttributeChannelAndLocale->fetch(
            AttributeIdentifier::fromString('attribute_without_channel_nor_locale'),
            ChannelIdentifier::fromCode('ecommerce'),
            LocaleIdentifier::fromCode('fr_FR')
        );
        $this->assertEquals('attribute_without_channel_nor_locale', (string)$valueKey);
    }

    /**
     * @test
     */
    public function it_returns_the_value_key_for_attributes_with_value_per_channel()
    {
        $this->loadAttributeWithValuePerChannel('attribute_with_value_per_channel');
        $valueKey = $this->getValueKeyForAttributeChannelAndLocale->fetch(
            AttributeIdentifier::fromString('attribute_with_value_per_channel'),
            ChannelIdentifier::fromCode('ecommerce'),
            LocaleIdentifier::fromCode('fr_FR')
        );
        $this->assertEquals('attribute_with_value_per_channel_ecommerce', (string)$valueKey);
    }

    /**
     * @test
     */
    public function it_returns_the_value_key_for_attributes_with_value_per_locale()
    {
        $this->loadAttributeWithValuePerLocale('attribute_with_value_per_locale');
        $valueKey = $this->getValueKeyForAttributeChannelAndLocale->fetch(
            AttributeIdentifier::fromString('attribute_with_value_per_locale'),
            ChannelIdentifier::fromCode('ecommerce'),
            LocaleIdentifier::fromCode('fr_FR')
        );
        $this->assertEquals('attribute_with_value_per_locale_fr_FR', (string)$valueKey);
    }

    /**
     * @test
     */
    public function it_returns_the_value_key_for_attributes_with_value_per_channel_and_value_per_locale()
    {
        $this->loadAttributeWithValuePerChannelAndLocale('attribute_with_value_per_channel_and_locale');
        $valueKey = $this->getValueKeyForAttributeChannelAndLocale->fetch(
            AttributeIdentifier::fromString('attribute_with_value_per_channel_and_locale'),
            ChannelIdentifier::fromCode('ecommerce'),
            LocaleIdentifier::fromCode('fr_FR')
        );
        $this->assertEquals('attribute_with_value_per_channel_and_locale_ecommerce_fr_FR', (string)$valueKey);
    }

    /**
     * @test
     */
    public function it_throws_if_the_value_key_is_not_found()
    {
        $this->expectException(\LogicException::class);
        $this->getValueKeyForAttributeChannelAndLocale->fetch(
            AttributeIdentifier::fromString('unknown_attribute'),
            ChannelIdentifier::fromCode('ecommerce'),
            LocaleIdentifier::fromCode('fr_FR')
        );
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadAssetFamilies(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $designer = AssetFamily::create(
            AssetFamilyIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository->create($designer);
    }

    private function loadAttributeWithoutValuePerChannelOrLocale(string $attributeIdentifier): void
    {
        $attribute = MediaFileAttribute::create(
            AttributeIdentifier::fromString($attributeIdentifier),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeIdentifier),
            LabelCollection::fromArray(['fr_FR' => 'dummy label']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::noLimit(),
            AttributeAllowedExtensions::fromList(['png']),
            MediaType::fromString(MediaType::IMAGE)
        );

        $this->attributeRepository->create($attribute);
    }

    private function loadAttributeWithValuePerChannel(string $attributeIdentifier): void
    {
        $attribute = MediaFileAttribute::create(
            AttributeIdentifier::fromString($attributeIdentifier),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeIdentifier),
            LabelCollection::fromArray(['fr_FR' => 'dummy label']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::noLimit(),
            AttributeAllowedExtensions::fromList(['png']),
            MediaType::fromString(MediaType::IMAGE)
        );

        $this->attributeRepository->create($attribute);
    }

    private function loadAttributeWithValuePerLocale(string $attributeIdentifier): void
    {
        $attribute = OptionCollectionAttribute::create(
            AttributeIdentifier::fromString($attributeIdentifier),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeIdentifier),
            LabelCollection::fromArray(['fr_FR' => 'dummy label']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );

        $this->attributeRepository->create($attribute);
    }

    private function loadAttributeWithValuePerChannelAndLocale(string $attributeIdentifier): void
    {
        $attribute = TextAttribute::createText(
            AttributeIdentifier::fromString($attributeIdentifier),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeIdentifier),
            LabelCollection::fromArray(['fr_FR' => 'dummy label']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(25),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $this->attributeRepository->create($attribute);
    }
}

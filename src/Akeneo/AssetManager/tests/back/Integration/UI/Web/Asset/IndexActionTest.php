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

namespace Akeneo\AssetManager\Integration\UI\Web\Asset;

use Akeneo\AssetManager\Common\Fake\InMemoryFindRequiredValueKeyCollectionForChannelAndLocales;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\OptionData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
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
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Integration\ControllerIntegrationTestCase;

class IndexActionTest extends ControllerIntegrationTestCase
{
    private WebClientHelper $webClientHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures();
        $this->get('akeneoasset_manager.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoasset_manager.tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_assets_with_full_text_search()
    {
        $this->webClientHelper->assertRequest($this->client, 'Asset/Search/ok.json');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_assets_filtered_by_code_or_label()
    {
        $this->webClientHelper->assertRequest($this->client, 'Asset/Search/code_label_and_code_filtered.json');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_assets_filtered_by_code_inclusive()
    {
        $this->webClientHelper->assertRequest($this->client, 'Asset/Search/code_filtered.json');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_assets_filtered_by_option()
    {
        $this->webClientHelper->assertRequest($this->client, 'Asset/Search/color_filtered.json');
    }

    /**
     * @test
     */
    public function it_returns_an_empty_list()
    {
        $this->webClientHelper->assertRequest($this->client, 'Asset/Search/no_result.json');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_assets_filtered_by_complete()
    {
        $this->webClientHelper->assertRequest($this->client, 'Asset/Search/complete_filtered.json');
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_assets_filtered_by_uncomplete()
    {
        $this->webClientHelper->assertRequest($this->client, 'Asset/Search/uncomplete_filtered.json');
    }

    /**
     * @test
     */
    public function it_fails_if_invalid_asset_family_identifier()
    {
        $this->webClientHelper->assertRequest($this->client, 'Asset/Search/invalid_asset_family_identifier.json');
    }

    /**
     * @test
     */
    public function it_fails_if_desynchronized_asset_family_identifier()
    {
        $this->webClientHelper->assertRequest($this->client, 'Asset/Search/desynchronized_asset_family_identifier.json');
    }

    private function loadFixtures(): void
    {
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create('designer', 'description', '29aea250-bc94-49b2-8259-bbc116410eb2'),
                AssetFamilyIdentifier::fromString('designer'),
                AttributeCode::fromString('description'),
                LabelCollection::fromArray(['fr_FR' => 'Nom']),
                AttributeOrder::fromInteger(4),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeMaxLength::fromInteger(512),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
        $attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create('designer', 'nickname', '29aea250-bc94-49b2-8259-bbc116410eb2'),
                AssetFamilyIdentifier::fromString('designer'),
                AttributeCode::fromString('nickname'),
                LabelCollection::fromArray(['fr_FR' => 'Surnom']),
                AttributeOrder::fromInteger(3),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(512),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
        $attributeRepository->create(
            OptionAttribute::create(
                AttributeIdentifier::create('designer', 'colors', '52609e00b7ee307e79eb100099b9a8bf'),
                AssetFamilyIdentifier::fromString('designer'),
                AttributeCode::fromString('colors'),
                LabelCollection::fromArray(['en_US' => 'Color']),
                AttributeOrder::fromInteger(5),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false)
            )
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamilyRepository->create(
            AssetFamily::create(
                $assetFamilyIdentifier,
                [],
                Image::createEmpty(),
                RuleTemplateCollection::empty()
            )
        );

        $cityAssetFamilyIdentifier = AssetFamilyIdentifier::fromString('city');
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamilyRepository->create(
            AssetFamily::create(
                $cityAssetFamilyIdentifier,
                [],
                Image::createEmpty(),
                RuleTemplateCollection::empty()
            )
        );
        /** @var AssetFamily $assetFamily */
        $assetFamily = $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $attributeAsLabelIdentifier = $assetFamily->getAttributeAsLabelReference()->getIdentifier();

        // STARCK
        $assetCode = AssetCode::fromString('starck');
        $identifier = AssetIdentifier::fromString('designer_starck_29aea250-bc94-49b2-8259-bbc116410eb2');

        $labelValueEnUS = Value::create(
            $attributeAsLabelIdentifier,
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('Starck')
        );
        $starckDescriptionValue = Value::create(
            AttributeIdentifier::fromString('description_designer_29aea250-bc94-49b2-8259-bbc116410eb2'),
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('an awesome designer!')
        );
        $starkColorValue = Value::create(
            AttributeIdentifier::create('designer', 'colors', '52609e00b7ee307e79eb100099b9a8bf'),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            OptionData::createFromNormalize('red')
        );

        $assetStarck = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([$labelValueEnUS, $starckDescriptionValue, $starkColorValue])
        );
        $assetRepository->create($assetStarck);

        // COCO
        $assetCode = AssetCode::fromString('coco');
        $identifier = AssetIdentifier::fromString('brand_coco_0134dc3e-3def-4afr-85ef-e81b2d6e95fd');

        $cocoDescriptionValue = Value::create(
            AttributeIdentifier::fromString('description_designer_29aea250-bc94-49b2-8259-bbc116410eb2'),
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('You will love coco.')
        );
        $cocoNicknameValue = Value::create(
            AttributeIdentifier::fromString('nickname_designer_29aea250-bc94-49b2-8259-bbc116410eb2'),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            TextData::fromString('Mr coco')
        );
        $labelValueEnUS = Value::create(
            $attributeAsLabelIdentifier,
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('Coco Chanel')
        );
        $labelValuefrFR = Value::create(
            $attributeAsLabelIdentifier,
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('Coco Chanel')
        );

        $assetCoco = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([$labelValueEnUS, $labelValuefrFR, $cocoDescriptionValue, $cocoNicknameValue])
        );
        $assetRepository->create($assetCoco);

        // DYSON
        $assetCode = AssetCode::fromString('dyson');
        $identifier = AssetIdentifier::fromString('designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd');

        $labelValueEnUS = Value::create(
            $attributeAsLabelIdentifier,
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('Dyson')
        );
        $labelValuefrFR = Value::create(
            $attributeAsLabelIdentifier,
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('Dyson')
        );
        $dysonColorValue = Value::create(
            AttributeIdentifier::create('designer', 'colors', '52609e00b7ee307e79eb100099b9a8bf'),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            OptionData::createFromNormalize('red')
        );
        $assetDyson = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([$labelValueEnUS, $labelValuefrFR, $dysonColorValue])
        );
        $assetRepository->create($assetDyson);

        // Paris
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('city');
        /** @var AssetFamily $assetFamily */
        $assetFamily = $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $attributeAsLabelIdentifier = $assetFamily->getAttributeAsLabelReference()->getIdentifier();
        $assetCode = AssetCode::fromString('paris');
        $identifier = AssetIdentifier::fromString('city_paris_bf11a6b3-3e46-4bbf-b35c-814a0020c717');
        $labelValueEnUS = Value::create(
            $attributeAsLabelIdentifier,
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('Paris')
        );
        $assetParis = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([$labelValueEnUS])
        );
        $assetRepository->create($assetParis);

        /** @var InMemoryFindRequiredValueKeyCollectionForChannelAndLocales $findRequiredKeyCollectionQuery */
        $findRequiredKeyCollectionQuery = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_required_value_key_collection_for_channel_and_locales');
        $findRequiredKeyCollectionQuery->setActivatedLocales(['en_US', 'fr_FR']);
        $findRequiredKeyCollectionQuery->setActivatedChannels(['ecommerce']);
    }
}

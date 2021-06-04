<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Persistence\Sql\Attribute;

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
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\SqlFindValueKeysToIndexForAllChannelsAndLocales;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindValueKeysToIndexForAllChannelsAndLocalesTest extends SqlIntegrationTestCase
{
    private SqlFindValueKeysToIndexForAllChannelsAndLocales $findValuesToIndexForChannelAndLocale;

    public function setUp(): void
    {
        parent::setUp();

        $this->findValuesToIndexForChannelAndLocale = $this->get('akeneo_assetmanager.infrastructure.search.elasticsearch.asset.query.find_values_to_index_for_channel_and_locale');
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_generates_an_empty_list()
    {
        $valueKeyCollection = $this->findValuesToIndexForChannelAndLocale->find(AssetFamilyIdentifier::fromString('designer'));
        Assert::assertEquals(
            [
                'ecommerce' => ['fr_FR' => [], 'en_US' => []],
                'mobile'    => ['de_DE' => []],
                'print'     => ['en_US' => []],
            ],
            $valueKeyCollection
        );
    }

    /**
     * @test
     */
    public function it_generates_a_list_of_value_keys_of_text_attributes_only()
    {
        $this->loadAssetFamilyAndAttributes();
        $valueKeyCollection = $this->findValuesToIndexForChannelAndLocale->find(
            AssetFamilyIdentifier::fromString('designer')
        );

        /** @var AssetFamily $assetFamily */
        $assetFamily = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family')
            ->getByIdentifier(AssetFamilyIdentifier::fromString('designer'));
        $attributeAsLabelIdentifier = $assetFamily->getAttributeAsLabelReference()->getIdentifier();

        Assert::assertEquals(
            [
                'ecommerce' => [
                    'fr_FR' => [
                        sprintf('%s_fr_FR', $attributeAsLabelIdentifier),
                        'name_designer_fingerprint_ecommerce_fr_FR',
                    ],
                    'en_US' => [
                        sprintf('%s_en_US', $attributeAsLabelIdentifier),
                        'name_designer_fingerprint_ecommerce_en_US',
                    ],
                ],
                'mobile'    => [
                    'de_DE' => [
                        sprintf('%s_de_DE', $attributeAsLabelIdentifier),
                        'name_designer_fingerprint_mobile_de_DE',
                    ],
                ],
                'print'     => [
                    'en_US' => [
                        sprintf('%s_en_US', $attributeAsLabelIdentifier),
                        'name_designer_fingerprint_print_en_US',
                    ],
                ],
            ],
            $valueKeyCollection
        );
    }


    private function loadAssetFamilyAndAttributes(): void
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

        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $name = TextAttribute::createText(
            AttributeIdentifier::fromString('name_designer_fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(25),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $attributeRepository->create($name);

        $image = MediaFileAttribute::create(
            AttributeIdentifier::fromString('main_image_designer_fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('main_image'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::noLimit(),
            AttributeAllowedExtensions::fromList(['png']),
            MediaType::fromString(MediaType::IMAGE)
        );
        $attributeRepository->create($image);
    }
}

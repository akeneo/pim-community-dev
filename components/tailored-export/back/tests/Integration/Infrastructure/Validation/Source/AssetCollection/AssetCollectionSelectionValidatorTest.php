<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\Source\AssetCollection;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsMainMediaReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Source\AssetCollection\AssetCollectionSelectionConstraint;
use Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class AssetCollectionSelectionValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validSelection
     */
    public function test_it_does_not_build_violations_on_valid_selection(array $value, string $attributeCode): void
    {
        $violations = $this->getValidator()->validate(
            $value,
            new AssetCollectionSelectionConstraint(['attributeCode' => $attributeCode])
        );

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidSelection
     */
    public function test_it_builds_violations_on_invalid_selection(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value,
        string $attributeCode
    ): void {
        $violations = $this->getValidator()->validate(
            $value,
            new AssetCollectionSelectionConstraint(['attributeCode' => $attributeCode])
        );

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validSelection(): array
    {
        return [
            'a code selection' => [
                [
                    'type' => 'code',
                    'separator' => ';',
                ],
                'my_media_file_asset_collection',
            ],
            'a label selection' => [
                [
                    'type' => 'label',
                    'separator' => ',',
                    'locale' => 'en_US',
                ],
                'my_media_file_asset_collection',
            ],
            'a media file selection with property equals to file_key' => [
                [
                    'type' => 'media_file',
                    'separator' => '|',
                    'locale' => null,
                    'channel' => null,
                    'property' => 'file_key'
                ],
                'my_media_file_asset_collection',
            ],
            'a media file selection with property equals to file_path' => [
                [
                    'type' => 'media_file',
                    'separator' => '|',
                    'locale' => null,
                    'channel' => null,
                    'property' => 'file_path'
                ],
                'my_media_file_asset_collection',
            ],
            'a media file selection with property equals to original_filename' => [
                [
                    'type' => 'media_file',
                    'separator' => '|',
                    'locale' => null,
                    'channel' => null,
                    'property' => 'original_filename'
                ],
                'my_media_file_asset_collection',
            ],
            'a media link selection' => [
                [
                    'type' => 'media_link',
                    'separator' => '|',
                    'locale' => 'en_US',
                    'channel' => null,
                    'with_prefix_and_suffix' => true
                ],
                'my_localizable_media_link_asset_collection',
            ]
        ];
    }

    public function invalidSelection(): array
    {
        return [
            'invalid type' => [
                'The value you selected is not a valid choice.',
                '[type]',
                [
                    'type' => 'invalid type',
                    'separator' => ',',
                ],
                'my_media_file_asset_collection',
            ],
            'invalid separator' => [
                'The value you selected is not a valid choice.',
                '[separator]',
                [
                    'type' => 'code',
                    'separator' => 'foo',
                ],
                'my_media_file_asset_collection',
            ],
            'blank locale' => [
                'This value should not be blank.',
                '[locale]',
                [
                    'type' => 'label',
                    'separator' => ';',
                    'locale' => '',
                ],
                'my_media_file_asset_collection',
            ],
            'inactive locale' => [
                'akeneo.tailored_export.validation.locale.should_be_active',
                '[locale]',
                [
                    'type' => 'label',
                    'separator' => ';',
                    'locale' => 'fr_FR',
                ],
                'my_media_file_asset_collection',
            ],
            'media file without property' => [
                'This field is missing.',
                '[property]',
                [
                    'type' => 'media_file',
                    'separator' => '|',
                    'locale' => null,
                    'channel' => null,
                ],
                'my_media_file_asset_collection',
            ],
            'media file with invalid property' => [
                'The value you selected is not a valid choice.',
                '[property]',
                [
                    'type' => 'media_file',
                    'separator' => '|',
                    'locale' => null,
                    'channel' => null,
                    'property' => 'file_kéké'
                ],
                'my_media_file_asset_collection',
            ],
            'media file without locale' => [
                'This field is missing.',
                '[property]',
                [
                    'type' => 'media_file',
                    'separator' => '|',
                    'channel' => null,
                ],
                'my_media_file_asset_collection',
            ],
            'media file with inactive locale' => [
                'akeneo.tailored_export.validation.locale.should_be_active',
                '[locale]',
                [
                    'type' => 'media_file',
                    'separator' => '|',
                    'locale' => 'fr_FR',
                    'channel' => null,
                    'with_prefix_and_suffix' => true
                ],
                'my_localizable_media_link_asset_collection',
            ],
            'media file without channel' => [
                'This field is missing.',
                '[property]',
                [
                    'type' => 'media_file',
                    'separator' => '|',
                    'locale' => null,
                ],
                'my_media_file_asset_collection',
            ],
            'media file with unknown channel' => [
                'akeneo.tailored_export.validation.channel.should_exist',
                '[channel]',
                [
                    'type' => 'media_file',
                    'separator' => '|',
                    'locale' => null,
                    'channel' => 'canal+',
                    'with_prefix_and_suffix' => true
                ],
                'my_scopable_media_file_asset_collection',
            ],
            'media link without with_prefix_and_suffix' => [
                'This field is missing.',
                '[with_prefix_and_suffix]',
                [
                    'type' => 'media_link',
                    'separator' => '|',
                    'locale' => null,
                    'channel' => null,
                ],
                'my_media_link_asset_collection',
            ],
            'media link with invalid with_prefix_and_suffix' => [
                'This value should be of type bool.',
                '[with_prefix_and_suffix]',
                [
                    'type' => 'media_link',
                    'separator' => '|',
                    'locale' => null,
                    'channel' => null,
                    'with_prefix_and_suffix' => 'trou'
                ],
                'my_media_link_asset_collection',
            ],
            'media link without channel' => [
                'This field is missing.',
                '[channel]',
                [
                    'type' => 'media_link',
                    'separator' => '|',
                    'locale' => null,
                    'with_prefix_and_suffix' => true
                ],
                'my_media_link_asset_collection',
            ],
            'media link with unknown channel' => [
                'akeneo.tailored_export.validation.channel.should_exist',
                '[channel]',
                [
                    'type' => 'media_link',
                    'separator' => '|',
                    'locale' => null,
                    'channel' => 'canal+',
                    'with_prefix_and_suffix' => true
                ],
                'my_scopable_media_link_asset_collection',
            ],
            'media link without locale' => [
                'This field is missing.',
                '[locale]',
                [
                    'type' => 'media_link',
                    'separator' => '|',
                    'channel' => null,
                    'with_prefix_and_suffix' => true
                ],
                'my_media_link_asset_collection',
            ],
            'media link with inactive locale' => [
                'akeneo.tailored_export.validation.asset_collection.locale_should_be_blank',
                '[locale]',
                [
                    'type' => 'media_link',
                    'separator' => '|',
                    'locale' => 'fr_FR',
                    'channel' => null,
                    'with_prefix_and_suffix' => true
                ],
                'my_media_link_asset_collection',
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->get('feature_flags')->enable('asset_manager');
        $this->loadData();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function loadData(): void
    {
        $this->createAssetFamilyWithMediaFileAsMainMedia('asset_family_with_media_file_as_main_media', false, false);
        $this->createAttribute('my_media_file_asset_collection', 'asset_family_with_media_file_as_main_media');

        $this->createAssetFamilyWithMediaFileAsMainMedia('asset_family_with_scopable_media_file_as_main_media', true, false);
        $this->createAttribute('my_scopable_media_file_asset_collection', 'asset_family_with_scopable_media_file_as_main_media');

        $this->createAssetFamilyWithMediaLinkAsMainMedia('asset_family_with_media_link_as_main_media', false, false);
        $this->createAttribute('my_media_link_asset_collection', 'asset_family_with_media_link_as_main_media');

        $this->createAssetFamilyWithMediaLinkAsMainMedia('asset_family_with_scopable_media_link_as_main_media', true, false);
        $this->createAttribute('my_scopable_media_link_asset_collection', 'asset_family_with_scopable_media_link_as_main_media');

        $this->createAssetFamilyWithMediaLinkAsMainMedia('asset_family_with_localizable_media_link_as_main_media', false, true);
        $this->createAttribute('my_localizable_media_link_asset_collection', 'asset_family_with_localizable_media_link_as_main_media');
    }

    private function createAttribute(string $attributeCode, string $assetFamilyCode): void
    {
        $attributeAssetMultipleLink = $this->get('pim_catalog.factory.attribute')
            ->createAttribute(AssetCollectionType::ASSET_COLLECTION);

        $this->get('pim_catalog.updater.attribute')
            ->update($attributeAssetMultipleLink, [
                'code' => $attributeCode,
                'reference_data_name' => $assetFamilyCode,
                'group' => 'other'
            ]);

        $errors = $this->get('validator')->validate($attributeAssetMultipleLink);
        if ($errors->count() > 0) {
            throw new \Exception(
                sprintf(
                    'Cannot create the attribute "%s": %s',
                    $attributeAssetMultipleLink->getCode(),
                    (string) $errors[0]
                )
            );
        }

        $this->get('pim_catalog.saver.attribute')->save($attributeAssetMultipleLink);
    }

    private function createAssetFamily(string $assetFamilyIdentifier, AbstractAttribute $attribute): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamilyRepository->create(
            AssetFamily::create(
                AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
                [],
                Image::createEmpty(),
                RuleTemplateCollection::empty()
            )
        );

        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $attributeRepository->create($attribute);

        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamily = $assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString($assetFamilyIdentifier));
        $assetFamily->updateAttributeAsMainMediaReference(AttributeAsMainMediaReference::fromAttributeIdentifier($attribute->getIdentifier()));
        $assetFamilyRepository->update($assetFamily);
    }

    private function createAssetFamilyWithMediaFileAsMainMedia(
        string $assetFamilyIdentifier,
        bool $valuePerChannel,
        bool $valuePerLocale
    ): void {
        $mediaLinkIdentifier = AttributeIdentifier::fromString(sprintf('%s_url', $assetFamilyIdentifier));
        $attribute = MediaFileAttribute::create(
            $mediaLinkIdentifier,
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString('url'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean($valuePerChannel),
            AttributeValuePerLocale::fromBoolean($valuePerLocale),
            AttributeMaxFileSize::noLimit(),
            AttributeAllowedExtensions::fromList(AttributeAllowedExtensions::ALL_ALLOWED),
            MediaType::fromString(MediaType::IMAGE)
        );

        $this->createAssetFamily($assetFamilyIdentifier, $attribute);
    }

    private function createAssetFamilyWithMediaLinkAsMainMedia(
        string $assetFamilyIdentifier,
        bool $valuePerChannel,
        bool $valuePerLocale
    ): void {
        $mediaLinkIdentifier = AttributeIdentifier::fromString(sprintf('%s_url', $assetFamilyIdentifier));
        $mediaLinkAttribute = MediaLinkAttribute::create(
            $mediaLinkIdentifier,
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString('url'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean($valuePerChannel),
            AttributeValuePerLocale::fromBoolean($valuePerLocale),
            Prefix::createEmpty(),
            Suffix::createEmpty(),
            MediaLink\MediaType::fromString(MediaLink\MediaType::PDF)
        );

        $this->createAssetFamily($assetFamilyIdentifier, $mediaLinkAttribute);
    }
}

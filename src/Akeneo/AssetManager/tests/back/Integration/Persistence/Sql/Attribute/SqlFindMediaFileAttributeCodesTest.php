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
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\FindMediaFileAttributeCodesInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlFindMediaFileAttributeCodesTest extends SqlIntegrationTestCase
{
    private FindMediaFileAttributeCodesInterface $findMediaFileAttributeCodes;

    private AttributeRepositoryInterface $attributesRepository;

    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->findMediaFileAttributeCodes = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_media_file_attribute_codes');
        $this->attributesRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $this->assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');

        $this->resetDB();
        $this->loadAssetFamily();
    }

    /**
     * @test
     */
    public function it_returns_the_codes_of_the_media_file_attributes_for_a_given_asset_family()
    {
        $this->loadAttributesWithMediaFileType();

        $mediaFileAttributeCodes = $this->findMediaFileAttributeCodes->find(AssetFamilyIdentifier::fromString('designer'));
        $expectedCodes = [
            AttributeCode::fromString(AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE),
            AttributeCode::fromString('main_image'),
            AttributeCode::fromString('second_image')
        ];

        $this->assertEquals($expectedCodes, $mediaFileAttributeCodes);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_there_is_no_media_file_attribute()
    {
        $this->loadAttributesWithoutImageType();

        $mediaFileAttributeCodes = $this->findMediaFileAttributeCodes->find(AssetFamilyIdentifier::fromString('designer'));

        $this->assertSame([], $mediaFileAttributeCodes);
    }

    private function loadAssetFamily(): void
    {
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->create($assetFamily);
    }

    private function loadAttributesWithMediaFileType(): void
    {
        $mediaFileAttribute = MediaFileAttribute::create(
            AttributeIdentifier::create('designer', AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE, 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('main_image'),
            LabelCollection::fromArray(['en_US' => 'Portrait']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString('1000'),
            AttributeAllowedExtensions::fromList(['pdf']),
            MediaType::fromString(MediaType::PDF)
        );

        $secondMediaFileAttribute = MediaFileAttribute::create(
            AttributeIdentifier::create('designer', 'second_image', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('second_image'),
            LabelCollection::fromArray(['en_US' => 'Portrait']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString('1000'),
            AttributeAllowedExtensions::fromList(['pdf']),
            MediaType::fromString(MediaType::PDF)
        );

        $assetFamily = $this->assetFamilyRepository
            ->getByIdentifier(AssetFamilyIdentifier::fromString('designer'));

        $this->attributesRepository->create($mediaFileAttribute);
        $this->attributesRepository->create($secondMediaFileAttribute);
        $this->attributesRepository->deleteByIdentifier($assetFamily->getAttributeAsLabelReference()->getIdentifier());
    }

    private function loadAttributesWithoutImageType()
    {
        $name = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(4),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $email = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'email', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('email'),
            LabelCollection::fromArray(['en_US' => 'Email']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::EMAIL),
            AttributeRegularExpression::createEmpty()
        );

        $assetFamily = $this->assetFamilyRepository
            ->getByIdentifier(AssetFamilyIdentifier::fromString('designer'));

        $this->attributesRepository->create($name);
        $this->attributesRepository->create($email);
        $this->attributesRepository->deleteByIdentifier($assetFamily->getAttributeAsMainMediaReference()->getIdentifier());
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }
}

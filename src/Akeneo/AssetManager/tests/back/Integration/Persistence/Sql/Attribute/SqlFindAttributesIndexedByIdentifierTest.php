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
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRichTextEditor;
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
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlFindAttributesIndexedByIdentifierTest extends SqlIntegrationTestCase
{
    private FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier;

    /** @var AbstractAttribute */
    private $name;

    private AbstractAttribute $email;

    private AbstractAttribute $customRegex;

    private AbstractAttribute $longDescription;

    private ?MediaFileAttribute $mediaFileAttribute = null;

    private AbstractAttribute $attributeAsLabel;

    private AbstractAttribute $attributeAsMainMedia;

    public function setUp(): void
    {
        parent::setUp();

        $this->findAttributesIndexedByIdentifier = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_attributes_indexed_by_identifier');
        $this->resetDB();
        $this->loadAssetFamiliesAndAttributes();
    }

    /**
     * @test
     */
    public function it_returns_the_attributes_indexed_by_identifier_for_an_asset_family()
    {
        $actualAttributes = $this->findAttributesIndexedByIdentifier->find(AssetFamilyIdentifier::fromString('designer'));

        $expectedAttributes = [
            'name_designer_test'             => $this->name,
            'email_designer_test'            => $this->email,
            'long_description_designer_test' => $this->longDescription,
            'regex_designer_test'            => $this->customRegex,
            'image_designer_test'            => $this->mediaFileAttribute,
            $this->attributeAsLabel->getIdentifier()->normalize() => $this->attributeAsLabel,
            $this->attributeAsMainMedia->getIdentifier()->normalize() => $this->attributeAsMainMedia,
        ];
        $this->assertCount(7, $actualAttributes);
        foreach ($expectedAttributes as $expectedIdentifier => $expectedAttribute) {
            $this->assertArrayHasKey($expectedIdentifier, $actualAttributes);
            $this->assertSame($expectedAttribute->normalize(), $actualAttributes[$expectedIdentifier]->normalize());
        }
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_the_asset_family_does_not_have_any_attributes()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand');
        $attributeDetails = $this->findAttributesIndexedByIdentifier->find($assetFamilyIdentifier);
        $this->assertCount(0, $attributeDetails);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadAssetFamiliesAndAttributes(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamilyWithAttributes = AssetFamily::create(
            AssetFamilyIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyWithoutAttributes = AssetFamily::create(
            AssetFamilyIdentifier::fromString('brand'),
            [
                'fr_FR' => 'Marque',
                'en_US' => 'Brand',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository->create($assetFamilyWithAttributes);
        $assetFamilyWithAttributes = $assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString('designer'));
        $assetFamilyRepository->create($assetFamilyWithoutAttributes);
        $assetFamilyWithoutAttributes = $assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString('brand'));

        $attributesRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $this->name = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->email = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'email', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('email'),
            LabelCollection::fromArray(['en_US' => 'Email']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::EMAIL),
            AttributeRegularExpression::createEmpty()
        );
        $this->customRegex = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'regex', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('regex'),
            LabelCollection::fromArray(['en_US' => 'Regex']),
            AttributeOrder::fromInteger(4),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegularExpression::fromString('/\w+/')
        );
        $this->longDescription = TextAttribute::createTextarea(
            AttributeIdentifier::create('designer', 'long_description', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('long_description'),
            LabelCollection::fromArray(['en_US' => 'Long description']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeIsRichTextEditor::fromBoolean(true)
        );
        $this->mediaFileAttribute = MediaFileAttribute::create(
            AttributeIdentifier::create('designer', 'image', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('main_image'),
            LabelCollection::fromArray(['en_US' => 'Portrait']),
            AttributeOrder::fromInteger(6),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString('1000'),
            AttributeAllowedExtensions::fromList(['pdf']),
            MediaType::fromString(MediaType::PDF)
        );
        $attributesRepository->create($this->name);
        $attributesRepository->create($this->email);
        $attributesRepository->create($this->customRegex);
        $attributesRepository->create($this->longDescription);
        $attributesRepository->create($this->mediaFileAttribute);

        $attributesRepository->deleteByIdentifier($assetFamilyWithoutAttributes->getAttributeAsLabelReference()->getIdentifier());
        $attributesRepository->deleteByIdentifier($assetFamilyWithoutAttributes->getAttributeAsMainMediaReference()->getIdentifier());

        $this->attributeAsLabel = $attributesRepository->getByIdentifier($assetFamilyWithAttributes->getAttributeAsLabelReference()->getIdentifier());
        $this->attributeAsMainMedia = $attributesRepository->getByIdentifier($assetFamilyWithAttributes->getAttributeAsMainMediaReference()->getIdentifier());
    }
}

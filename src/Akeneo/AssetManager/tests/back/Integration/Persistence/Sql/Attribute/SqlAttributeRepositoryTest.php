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

use Akeneo\AssetManager\Domain\Event\AttributeDeletedEvent;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeDecimalsAllowed;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeLimit;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType as MediaFileMediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType as MediaLinkMediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use AkeneoEnterprise\Test\IntegrationTestsBundle\EventDispatcher\EventDispatcherMock;
use Doctrine\DBAL\DBALException;

class SqlAttributeRepositoryTest extends SqlIntegrationTestCase
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var EventDispatcherMock */
    private $eventDispatcherMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $this->eventDispatcherMock = $this->get('event_dispatcher');
        $this->eventDispatcherMock->reset();

        $this->resetDB();
        $this->insertAssetFamily();
    }

    /**
     * @test
     */
    public function it_creates_an_attribute_of_type_text_and_returns_it()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('name');
        $identifier = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($assetFamilyIdentifier, $attributeCode);

        $expectedAttribute = TextAttribute::createTextarea(
            $identifier,
            $assetFamilyIdentifier,
            $attributeCode,
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
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
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('picture');
        $identifier = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($assetFamilyIdentifier, $attributeCode);

        $expectedAttribute = MediaFileAttribute::create(
            $identifier,
            $assetFamilyIdentifier,
            AttributeCode::fromString('picture'),
            LabelCollection::fromArray(['en_US' => 'Picture', 'fr_FR' => 'Image']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('250.12'),
            AttributeAllowedExtensions::fromList(['pdf', 'png']),
            MediaFileMediaType::fromString(MediaFileMediaType::IMAGE)
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
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('favorite_color');
        $identifier = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($assetFamilyIdentifier, $attributeCode);

        $expectedOption = OptionAttribute::create(
            $identifier,
            $assetFamilyIdentifier,
            $attributeCode,
            LabelCollection::fromArray(['en_US' => 'Favorite Color', 'fr_FR' => 'Couleur favorite']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
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
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('colors');
        $identifier = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($assetFamilyIdentifier, $attributeCode);

        $expectedOption = OptionCollectionAttribute::create(
            $identifier,
            $assetFamilyIdentifier,
            AttributeCode::fromString('colors'),
            LabelCollection::fromArray(['en_US' => 'Colors', 'fr_FR' => 'Couleurs']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
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
    public function it_creates_an_attribute_of_type_number_and_returns_it()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $identifier = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($assetFamilyIdentifier, AttributeCode::fromString('number'));
        $expectedNumber = NumberAttribute::create(
            $identifier,
            $assetFamilyIdentifier,
            AttributeCode::fromString('number'),
            LabelCollection::fromArray(['en_US' => 'Colors', 'fr_FR' => 'Couleurs']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeDecimalsAllowed::fromBoolean(true),
            AttributeLimit::fromString('10'),
            AttributeLimit::limitless()
        );

        $this->attributeRepository->create($expectedNumber);

        $actualNumber = $this->attributeRepository->getByIdentifier($identifier);
        $this->assertAttribute($expectedNumber, $actualNumber);
    }

    /**
     * @test
     */
    public function it_creates_an_attribute_of_type_mediaLink_and_returns_it()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $identifier = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($assetFamilyIdentifier, AttributeCode::fromString('number'));
        $expectedMediaLink = MediaLinkAttribute::create(
            $identifier,
            $assetFamilyIdentifier,
            AttributeCode::fromString('preview'),
            LabelCollection::fromArray(['en_US' => 'Preview', 'fr_FR' => 'Aperçu']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::fromString('http://google.com/'),
            Suffix::empty(),
            MediaLinkMediaType::fromString('image')
        );

        $this->attributeRepository->create($expectedMediaLink);

        $actualMediaLink = $this->attributeRepository->getByIdentifier($identifier);
        $this->assertAttribute($expectedMediaLink, $actualMediaLink);
    }

    /**
     * @test
     */
    public function it_throws_when_creating_an_attribute_with_the_same_identifier()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('name');
        $identifier = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($assetFamilyIdentifier, $attributeCode);

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
        $identifier = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute')
            ->nextIdentifier(AssetFamilyIdentifier::fromString('designer'), AttributeCode::fromString('bio'));

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
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('name');
        $identifier = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($assetFamilyIdentifier, $attributeCode);

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
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $expectedAttribute = TextAttribute::createTextarea(
            $identifier,
            $assetFamilyIdentifier,
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Description', 'fr_FR' => 'Description']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(255),
            AttributeIsRichTextEditor::fromBoolean(false)
        );
        $this->attributeRepository->create($expectedAttribute);

        $expectedAttribute->updateLabels(LabelCollection::fromArray(['fr_FR' => 'Biography', 'en_US' => 'Biographie']));
        $expectedAttribute->setMaxLength(AttributeMaxLength::fromInteger(100));
        $expectedAttribute->setIsRichTextEditor(AttributeIsRichTextEditor::fromBoolean(true));
        $expectedAttribute->setIsRequired(AttributeIsRequired::fromBoolean(false));
        $expectedAttribute->setIsReadOnly(AttributeIsReadOnly::fromBoolean(true));
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
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $expectedAttribute = TextAttribute::createText(
            $identifier,
            $assetFamilyIdentifier,
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
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
    public function it_counts_all_asset_family_attributes()
    {
        $designerIdentifier = AssetFamilyIdentifier::fromString('designer');

        $this->assertEquals(2, $this->attributeRepository->countByAssetFamily($designerIdentifier));

        $identifier = AttributeIdentifier::create('designer', 'name', 'test');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $expectedAttribute = TextAttribute::createText(
            $identifier,
            $assetFamilyIdentifier,
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(255),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->attributeRepository->create($expectedAttribute);

        $this->assertEquals(3, $this->attributeRepository->countByAssetFamily($designerIdentifier));

        $identifier = AttributeIdentifier::create('designer', 'name2', 'test');
        $expectedAttribute = TextAttribute::createText(
            $identifier,
            $assetFamilyIdentifier,
            AttributeCode::fromString('name2'),
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(255),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->attributeRepository->create($expectedAttribute);

        $this->assertEquals(4, $this->attributeRepository->countByAssetFamily($designerIdentifier));
    }

    /**
     * @test
     */
    public function it_returns_attribute_from_its_code_and_asset_family_identifier()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('name');
        $identifier = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($assetFamilyIdentifier, $attributeCode);
        $textAttribute = $this->createAttributeWithIdentifier($identifier);
        $this->attributeRepository->create($textAttribute);

        $this->assertEquals(
            $this->attributeRepository->getByCodeAndAssetFamilyIdentifier($attributeCode, $assetFamilyIdentifier),
            $textAttribute
        );

        $this->expectException(AttributeNotFoundException::class);
        $this->attributeRepository->getByCodeAndAssetFamilyIdentifier($attributeCode, AssetFamilyIdentifier::fromString('foo'));
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
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function insertAssetFamily(): void
    {
        $repository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer'
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $repository->create($assetFamily);
    }

    private function insertRowWithUnsupportedType(): AttributeIdentifier
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $attributeCode = AttributeCode::fromString('age');
        $identifier = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute')
            ->nextIdentifier($assetFamilyIdentifier, $attributeCode);

        $sqlConnection = $this->get('database_connection');
        $query = <<<SQL
        INSERT INTO akeneo_asset_manager_attribute (
            identifier,
            code,
            asset_family_identifier,
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
            :asset_family_identifier,
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
                'asset_family_identifier' => (string) $assetFamilyIdentifier,
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
    }
}

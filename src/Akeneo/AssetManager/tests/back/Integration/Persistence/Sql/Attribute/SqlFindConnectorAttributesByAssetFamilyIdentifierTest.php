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
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\FindConnectorAttributesByAssetFamilyIdentifierInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

class SqlFindConnectorAttributesByAssetFamilyIdentifierTest extends SqlIntegrationTestCase
{
    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var FindConnectorAttributesByAssetFamilyIdentifierInterface*/
    private $findConnectorAssetFamilyAttributes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $this->attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $this->findConnectorAssetFamilyAttributes = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_connector_asset_family_attributes_by_asset_family_identifier');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_finds_connector_attributes_for_an_asset_family()
    {
        $this->createAssetFamily('asset_family');
        $connectorAttributes = $this->createConnectorAttributes('asset_family');

        $foundAttributes = $this->findConnectorAssetFamilyAttributes->find(AssetFamilyIdentifier::fromString('asset_family'));

        $normalizedAttributes = [];
        foreach ($connectorAttributes as $attribute) {
            $normalizedAttributes[] = $attribute->normalize();
        }

        $normalizedFoundAttributes = [];
        foreach ($foundAttributes as $foundAttribute) {
            $normalizedFoundAttributes[] = $foundAttribute->normalize();
        }

        $this->assertEquals($normalizedAttributes, $normalizedFoundAttributes);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_no_attributes_found()
    {
        $foundAttributes = $this->findConnectorAssetFamilyAttributes->find(AssetFamilyIdentifier::fromString('whatever'));

        $this->assertSame([], $foundAttributes);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function createConnectorAttributes(string $assetFamilyIdentifier)
    {
        $textAttribute = TextAttribute::createText(
            AttributeIdentifier::create($assetFamilyIdentifier, 'text', 'test'),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString('regex'),
            LabelCollection::fromArray(['en_US' => 'Description', 'fr_FR' => 'Description']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegularExpression::fromString('/\w+/')
        );

        $imageAttribute = ImageAttribute::create(
            AttributeIdentifier::create($assetFamilyIdentifier, 'main_image', 'test'),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString('main_image'),
            LabelCollection::fromArray(['en_US' => 'Photo', 'fr_FR' => 'Photo']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('10'),
            AttributeAllowedExtensions::fromList(['jpg'])
        );

        $this->attributeRepository->create($textAttribute);
        $this->attributeRepository->create($imageAttribute);

        $assetFamily = $this->assetFamilyRepository
            ->getByIdentifier(AssetFamilyIdentifier::fromString($assetFamilyIdentifier));
        $attributeAsLabelIdentifier = $assetFamily->getAttributeAsLabelReference()->getIdentifier();
        $attributeAsImageIdentifier = $assetFamily->getAttributeAsImageReference()->getIdentifier();

        $attributeAsLabel = $this->attributeRepository->getByIdentifier($attributeAsLabelIdentifier);
        $attributeAsImage = $this->attributeRepository->getByIdentifier($attributeAsImageIdentifier);

        return [
            new ConnectorAttribute(
                $attributeAsLabel->getCode(),
                LabelCollection::fromArray([]),
                'text',
                AttributeValuePerLocale::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeIsRequired::fromBoolean(false),
                [
                    'max_length' => null,
                    'is_textarea' => false,
                    'is_rich_text_editor' => false,
                    'validation_rule' => AttributeValidationRule::NONE,
                    'regular_expression' => null
                ]
            ),
            new ConnectorAttribute(
                $attributeAsImage->getCode(),
                LabelCollection::fromArray([]),
                'image',
                AttributeValuePerLocale::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeIsRequired::fromBoolean(false),
                [
                    'max_file_size' => null,
                    'allowed_extensions' => []
                ]
            ),
            new ConnectorAttribute(
                $textAttribute->getCode(),
                LabelCollection::fromArray(['en_US' => 'Description', 'fr_FR' => 'Description']),
                'text',
                AttributeValuePerLocale::fromBoolean($textAttribute->hasValuePerLocale()),
                AttributeValuePerChannel::fromBoolean($textAttribute->hasValuePerChannel()),
                AttributeIsRequired::fromBoolean(true),
                [
                    'max_length' => $textAttribute->getMaxLength()->intValue(),
                    'is_textarea' => false,
                    'is_rich_text_editor' => false,
                    'validation_rule' => AttributeValidationRule::REGULAR_EXPRESSION,
                    'regular_expression' => $textAttribute->getRegularExpression()->normalize()
                ]
            ),
            new ConnectorAttribute(
                $imageAttribute->getCode(),
                LabelCollection::fromArray(['en_US' => 'Photo', 'fr_FR' => 'Photo']),
                'image',
                AttributeValuePerLocale::fromBoolean($imageAttribute->hasValuePerLocale()),
                AttributeValuePerChannel::fromBoolean($imageAttribute->hasValuePerChannel()),
                AttributeIsRequired::fromBoolean(true),
                [
                    'max_file_size' => '10',
                    'allowed_extensions' => ['jpg']
                ]
            ),
        ];
    }

    private function createAssetFamily(string $rawIdentifier): void
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($rawIdentifier);

        $imageInfo = new FileInfo();
        $imageInfo
            ->setOriginalFilename(sprintf('image_%s', $rawIdentifier))
            ->setKey(sprintf('test/image_%s.jpg', $rawIdentifier));

        $assetFamily = AssetFamily::create(
            $assetFamilyIdentifier,
            ['en_US' => $rawIdentifier],
            Image::fromFileInfo($imageInfo),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->create($assetFamily);
    }
}

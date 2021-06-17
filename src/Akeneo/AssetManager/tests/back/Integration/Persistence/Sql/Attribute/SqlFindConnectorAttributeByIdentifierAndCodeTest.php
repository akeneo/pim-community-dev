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
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\FindConnectorAttributeByIdentifierAndCodeInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

class SqlFindConnectorAttributeByIdentifierAndCodeTest extends SqlIntegrationTestCase
{
    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private AttributeRepositoryInterface $attributeRepository;

    private FindConnectorAttributeByIdentifierAndCodeInterface $findConnectorAssetFamilyAttribute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $this->attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $this->findConnectorAssetFamilyAttribute = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_connector_attribute_by_identifier_and_code');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_finds_a_connector_attribute_for_an_asset_family()
    {
        $assetFamilyIdentifier = 'asset_family';
        $this->createAssetFamily($assetFamilyIdentifier);
        $connectorAttribute = $this->createConnectorAttribute($assetFamilyIdentifier);

        $foundAttribute = $this->findConnectorAssetFamilyAttribute->find(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString('main_image')
        );

        $this->assertSame($connectorAttribute->normalize(), $foundAttribute->normalize());
    }

    /**
     * @test
     */
    public function it_returns_null_if_no_attribute_found()
    {
        $foundAttribute = $this->findConnectorAssetFamilyAttribute->find(
            AssetFamilyIdentifier::fromString('asset_family'),
            AttributeCode::fromString('none')
        );

        $this->assertSame(null, $foundAttribute);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function createConnectorAttribute(string $assetFamilyIdentifier)
    {
        $mediaFileAttribute = MediaFileAttribute::create(
            AttributeIdentifier::create($assetFamilyIdentifier, 'main_image', 'test'),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString('main_image'),
            LabelCollection::fromArray(['en_US' => 'Photo', 'fr_FR' => 'Photo', 'es_ES' => 'Foto']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('10'),
            AttributeAllowedExtensions::fromList(['jpg']),
            MediaType::fromString(MediaType::IMAGE)
        );

        $this->attributeRepository->create($mediaFileAttribute);

        return new ConnectorAttribute(
                $mediaFileAttribute->getCode(),
                LabelCollection::fromArray(['en_US' => 'Photo', 'fr_FR' => 'Photo']),
                'media_file',
                AttributeValuePerLocale::fromBoolean($mediaFileAttribute->hasValuePerLocale()),
                AttributeValuePerChannel::fromBoolean($mediaFileAttribute->hasValuePerChannel()),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsReadOnly::fromBoolean(false),
                [
                    'max_file_size' => '10',
                    'allowed_extensions' => ['jpg'],
                    'media_type' => MediaType::IMAGE
                ]
            );
    }

    private function createAssetFamily(string $rawIdentifier): AssetFamily
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

        return $assetFamily;
    }
}

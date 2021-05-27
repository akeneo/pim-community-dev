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

namespace Akeneo\AssetManager\Integration\Persistence\Sql\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConventionInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationLabel;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
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
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorAssetFamily;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorTransformation;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorTransformationCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\FindConnectorAssetFamilyByAssetFamilyIdentifierInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

class SqlFindConnectorAssetFamilyByAssetFamilyIdentifierTest extends SqlIntegrationTestCase
{
    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private AttributeRepositoryInterface $attributeRepository;

    private FindConnectorAssetFamilyByAssetFamilyIdentifierInterface $findConnectorAssetFamilyQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $this->attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $this->findConnectorAssetFamilyQuery = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_connector_asset_family_by_asset_family_identifier');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_finds_a_connector_asset_family()
    {
        $transformation = Transformation::create(
            TransformationLabel::fromString('label'),
            Source::createFromNormalized(['attribute' => 'main', 'channel' => null, 'locale' => null]),
            Target::createFromNormalized(['attribute' => 'target1', 'channel' => null, 'locale' => null]),
            OperationCollection::create([
                ThumbnailOperation::create(['width' => 100, 'height' => 80]),
                ColorspaceOperation::create(['colorspace' => 'grey']),
            ]),
            '1_',
            '_2',
            new \DateTime('1990-01-01')
        );
        $transformationCollection = TransformationCollection::create([$transformation]);
        $namingConvention = NamingConvention::createFromNormalized(
            [
                'source' => [
                    'property' => 'media',
                    'locale' => null,
                    'channel' => null,
                ],
                'pattern' => '/(pattern)/',
                'abort_asset_creation_on_error' => false,
            ]
        );
        $assetFamily = $this->createDesignerAssetFamily($transformationCollection, $namingConvention);

        $connectorTransformations = new ConnectorTransformationCollection([
            new ConnectorTransformation(
                TransformationLabel::fromString('label'),
                Source::createFromNormalized(['attribute' => 'main', 'channel' => null, 'locale' => null]),
                Target::createFromNormalized(['attribute' => 'target1', 'channel' => null, 'locale' => null]),
                OperationCollection::create([
                    ThumbnailOperation::create(['width' => 100, 'height' => 80]),
                    ColorspaceOperation::create(['colorspace' => 'grey']),
                ]),
                '1_',
                '_2'
            )
        ]);

        $expectedAssetFamily = new ConnectorAssetFamily(
            $assetFamily->getIdentifier(),
            LabelCollection::fromArray(['en_US' => 'designer', 'fr_FR' => 'designer']),
            Image::createEmpty(),
            [],
            $connectorTransformations,
            $namingConvention,
            AttributeCode::fromString('media')
        );

        $assetFamilyFound = $this->findConnectorAssetFamilyQuery->find(AssetFamilyIdentifier::fromString('designer'));

        $expectedAssetFamily = $expectedAssetFamily->normalize();
        $foundAssetFamily = $assetFamilyFound->normalize();

        $this->assertEquals($expectedAssetFamily, $foundAssetFamily);
    }

    /**
     * @test
     */
    public function it_returns_null_if_no_asset_family_found()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('whatever');
        $assetFamilyFound = $this->findConnectorAssetFamilyQuery->find($assetFamilyIdentifier);

        $this->assertNull($assetFamilyFound);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function createDesignerAssetFamily(
        TransformationCollection $transformationCollection,
        NamingConventionInterface $namingConvention
    ): AssetFamily {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');

        $imageInfo = new FileInfo();
        $imageInfo
            ->setOriginalFilename('image_2.jpg')
            ->setKey('test/image_2.jpg');

        $assetFamily = AssetFamily::create(
            $assetFamilyIdentifier,
            ['en_US' => 'designer', 'fr_FR' => 'designer'],
            Image::fromFileInfo($imageInfo),
            RuleTemplateCollection::empty()
        );
        $assetFamily = $assetFamily->withTransformationCollection($transformationCollection)
                                   ->withNamingConvention($namingConvention);

        $this->assetFamilyRepository->create($assetFamily);
        $this->createMediaFileAttribute('main', 'designer', 2);
        $this->createMediaFileAttribute('target1', 'designer', 3);

        return $assetFamily;
    }

    private function createMediaFileAttribute(string $attributeIdentifier, string $assetFamilyIdentifier, int $order)
    {
        $mediaFileAttribute = MediaFileAttribute::create(
            AttributeIdentifier::fromString($attributeIdentifier),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeIdentifier),
            LabelCollection::fromArray(['en_US' => $attributeIdentifier]),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('250.2'),
            AttributeAllowedExtensions::fromList(['jpg']),
            MediaType::fromString(MediaType::IMAGE)
        );

        $this->attributeRepository->create($mediaFileAttribute);
    }
}

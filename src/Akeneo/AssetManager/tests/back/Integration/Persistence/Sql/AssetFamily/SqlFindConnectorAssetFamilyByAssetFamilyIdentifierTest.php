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
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorAssetFamily;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\FindConnectorAssetFamilyByAssetFamilyIdentifierInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

class SqlFindConnectorAssetFamilyByAssetFamilyIdentifierTest extends SqlIntegrationTestCase
{
    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var FindConnectorAssetFamilyByAssetFamilyIdentifierInterface*/
    private $findConnectorAssetFamilyQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $this->findConnectorAssetFamilyQuery = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_connector_asset_family_by_asset_family_identifier');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_finds_a_connector_asset_family()
    {
        $transformation = Transformation::create(
            Source::createFromNormalized(['attribute' => 'main', 'channel' => null, 'locale' => null]),
            Target::createFromNormalized(['attribute' => 'target1', 'channel' => null, 'locale' => null]),
            OperationCollection::create([
                ThumbnailOperation::create(['width' => 100, 'height' => 80]),
                ColorspaceOperation::create(['colorspace' => 'grey']),
            ]),
            '1_',
            '_2'
        );
        $transformationCollection = TransformationCollection::create([$transformation]);

        $assetFamily = $this->createDesignerAssetFamily($transformationCollection);

        $expectedAssetFamily = new ConnectorAssetFamily(
            $assetFamily->getIdentifier(),
            LabelCollection::fromArray(['en_US' => 'designer', 'fr_FR' => 'designer']),
            Image::createEmpty(),
            [],
            $transformationCollection
        );

        $assetFamilyFound = $this->findConnectorAssetFamilyQuery->find(AssetFamilyIdentifier::fromString('designer'));

        $expectedAssetFamily = $expectedAssetFamily->normalize();
        $foundAssetFamily = $assetFamilyFound->normalize();

        $this->assertSame($expectedAssetFamily, $foundAssetFamily);
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

    private function createDesignerAssetFamily(TransformationCollection $transformationCollection): AssetFamily
    {
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
        $assetFamily = $assetFamily->withTransformationCollection($transformationCollection);

        $this->assetFamilyRepository->create($assetFamily);

        return $assetFamily;
    }
}

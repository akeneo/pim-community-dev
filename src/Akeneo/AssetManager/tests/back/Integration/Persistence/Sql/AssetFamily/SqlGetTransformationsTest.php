<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration\Persistence\Sql\AssetFamily;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ScaleOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\SqlGetTransformations;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

class SqlGetTransformationsTest extends SqlIntegrationTestCase
{
    /** @var SqlGetTransformations */
    private $sqlFindTransformationsForAsset;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var AssetRepositoryInterface */
    private $assetRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlFindTransformationsForAsset = $this->get(SqlGetTransformations::class);
        $this->assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $this->assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $this->resetDB();
    }

    public function test_it_returns_list_of_transformation_collection_for_a_list_of_asset_identifiers()
    {
        $transformationCollectionForAssetFamily1 = TransformationCollection::create([
            Transformation::create(
                Source::createFromNormalized(['attribute' => 'attr1', 'channel'=> null, 'locale' => null]),
                Target::createFromNormalized(['attribute' => 'attr2', 'channel'=> null, 'locale' => null]),
                OperationCollection::create([
                    ThumbnailOperation::create(['width' => 100, 'height' => 80]),
                    ColorspaceOperation::create(['colorspace' => 'grey']),
                ]),
                '1_',
                '_2'
            ),
        ]);
        $this->createAssetFamily('family1', $transformationCollectionForAssetFamily1);
        $this->createAsset('asset1', 'family1');
        $this->createAsset('asset2', 'family1');
        $this->createAsset('asset3', 'family1');

        $transformationCollectionForAssetFamily2 = TransformationCollection::create([
            Transformation::create(
                Source::createFromNormalized(['attribute' => 'attr1', 'channel'=> null, 'locale' => null]),
                Target::createFromNormalized(['attribute' => 'attr3', 'channel'=> null, 'locale' => null]),
                OperationCollection::create([
                    ScaleOperation::create(['ratio' => 80]),
                ]),
                '1_',
                '_2'
            ),
        ]);
        $this->createAssetFamily('family2', $transformationCollectionForAssetFamily2);
        $this->createAsset('asset4', 'family2');

        $results = $this->sqlFindTransformationsForAsset->fromAssetIdentifiers([
            AssetIdentifier::fromString('asset1'),
            AssetIdentifier::fromString('asset3'),
            AssetIdentifier::fromString('asset4'),
            AssetIdentifier::fromString('unknown'),
        ]);

        $this->assertCount(3, $results);
        $this->assertArrayHasKey('asset1', $results);
        $this->assertArrayHasKey('asset3', $results);
        $this->assertArrayHasKey('asset4', $results);

        $this->assertEquals($transformationCollectionForAssetFamily1, $results['asset1']);
        $this->assertEquals($transformationCollectionForAssetFamily1, $results['asset3']);
        $this->assertEquals($transformationCollectionForAssetFamily2, $results['asset4']);
    }

    public function test_it_returns_an_empty_collection()
    {
        $results = $this->sqlFindTransformationsForAsset->fromAssetIdentifiers([]);
        $this->assertEmpty($results);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function createAssetFamily(string $rawIdentifier, TransformationCollection $transformationCollection): void
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
        $assetFamily = $assetFamily->withTransformationCollection($transformationCollection);

        $this->assetFamilyRepository->create($assetFamily);
    }

    private function createAsset(string $rawIdentifier, string $rawFamilyIdentifier): void
    {
        $this->assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString($rawIdentifier),
                AssetFamilyIdentifier::fromString($rawFamilyIdentifier),
                AssetCode::fromString($rawIdentifier),
                ValueCollection::fromValues([])
            )
        );
    }
}

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

namespace Akeneo\AssetManager\Integration\Persistence\InMemory;

use Akeneo\AssetManager\Common\Fake\InMemoryAssetFamilyRepository;
use Akeneo\AssetManager\Common\Fake\InMemoryAssetRepository;
use Akeneo\AssetManager\Common\Fake\InMemoryGetTransformations;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsMainMediaReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryGetTransformationsTest extends TestCase
{
    /** @var InMemoryAssetRepository */
    private $assetRepository;

    /** @var InMemoryAssetFamilyRepository */
    private $assetFamilyRepository;

    /** @var InMemoryGetTransformations */
    private $inMemoryGetTransformations;

    protected function setUp(): void
    {
        parent::setUp();

        $eventDispatcher = new EventDispatcher();
        $this->assetRepository = new InMemoryAssetRepository($eventDispatcher);
        $this->assetFamilyRepository = new InMemoryAssetFamilyRepository($eventDispatcher);
        $this->inMemoryGetTransformations = new InMemoryGetTransformations(
            $this->assetRepository,
            $this->assetFamilyRepository
        );
    }

    public function test_it_returns_nothing()
    {
        $assetIdentifiers = [
            AssetIdentifier::fromString('asset1'),
            AssetIdentifier::fromString('asset2'),
        ];

        $results = $this->inMemoryGetTransformations->fromAssetIdentifiers($assetIdentifiers);
        $this->assertEquals([], $results);
    }

    public function test_it_returns_transformations()
    {
        $assetFamilyIdentifier1 = AssetFamilyIdentifier::fromString('family1');
        $transformationCollection1 = TransformationCollection::noTransformation();
        $this->createAssetFamily($assetFamilyIdentifier1, $transformationCollection1);

        $assetFamilyIdentifier2 = AssetFamilyIdentifier::fromString('family2');
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
        $transformationCollection2 = TransformationCollection::create([$transformation]);
        $this->createAssetFamily($assetFamilyIdentifier2, $transformationCollection2);

        $assetIdentifier1 = AssetIdentifier::fromString('asset1');
        $assetIdentifier2 = AssetIdentifier::fromString('asset2');
        $this->createAsset($assetIdentifier1, $assetFamilyIdentifier1);
        $this->createAsset($assetIdentifier2, $assetFamilyIdentifier2);

        $results = $this->inMemoryGetTransformations->fromAssetIdentifiers([
            $assetIdentifier1,
            $assetIdentifier2,
            AssetIdentifier::fromString('unknown')
        ]);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('asset1', $results);
        $this->assertArrayHasKey('asset2', $results);
        $this->assertEquals($transformationCollection1, $results['asset1']);
        $this->assertEquals($transformationCollection2, $results['asset2']);
    }

    private function createAssetFamily(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        TransformationCollection $transformationCollection
    ): void {
        $assetFamily = AssetFamily::createWithAttributes(
            $assetFamilyIdentifier,
            [],
            Image::createEmpty(),
            AttributeAsLabelReference::noReference(),
            AttributeAsMainMediaReference::noReference(),
            RuleTemplateCollection::empty()
        );
        $assetFamily = $assetFamily->withTransformationCollection($transformationCollection);
        $this->assetFamilyRepository->create($assetFamily);
    }

    private function createAsset(AssetIdentifier $assetIdentifier, AssetFamilyIdentifier $assetFamilyIdentifier): void
    {
        $this->assetRepository->create(
            Asset::create(
                $assetIdentifier,
                $assetFamilyIdentifier,
                AssetCode::fromString($assetIdentifier->__toString()),
                ValueCollection::fromValues([])
            )
        );
    }
}

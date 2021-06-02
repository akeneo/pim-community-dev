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

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationLabel;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\SqlGetTransformations;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PHPUnit\Framework\Assert;

class SqlGetTransformationsTest extends SqlIntegrationTestCase
{
    private SqlGetTransformations $getTransformations;

    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    public function test_it_throws_an_exception_if_the_asset_family_was_not_found()
    {
        $this->expectException(AssetFamilyNotFoundException::class);

        $this->getTransformations->fromAssetFamilyIdentifier(AssetFamilyIdentifier::fromString('unknown'));
    }

    public function test_it_returns_a_transformation_collection()
    {
        $assetFamilyIdentifier1 = AssetFamilyIdentifier::fromString('family1');
        $transformationCollection1 = TransformationCollection::noTransformation();
        $this->createAssetFamily((string)$assetFamilyIdentifier1, $transformationCollection1);

        $assetFamilyIdentifier2 = AssetFamilyIdentifier::fromString('family2');
        $transformation = Transformation::create(
            TransformationLabel::fromString('label'),
            Source::createFromNormalized(['attribute' => 'main', 'channel' => null, 'locale' => null]),
            Target::createFromNormalized(['attribute' => 'target1', 'channel' => null, 'locale' => null]),
            OperationCollection::create(
                [
                    ThumbnailOperation::create(['width' => 100, 'height' => 80]),
                    ColorspaceOperation::create(['colorspace' => 'grey']),
                ]
            ),
            '1_',
            '_2',
            new \DateTime()
        );
        $transformationCollection2 = TransformationCollection::create([$transformation]);
        $this->createAssetFamily((string)$assetFamilyIdentifier2, $transformationCollection2);

        $this->assertCollectionEqualsCollection(
            $transformationCollection1,
            $this->getTransformations->fromAssetFamilyIdentifier($assetFamilyIdentifier1)
        );
        $this->assertCollectionEqualsCollection(
            $transformationCollection2,
            $this->getTransformations->fromAssetFamilyIdentifier($assetFamilyIdentifier2)
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->getTransformations = $this->get(SqlGetTransformations::class);
        $this->assetFamilyRepository = $this->get(
            'akeneo_assetmanager.infrastructure.persistence.repository.asset_family'
        );
        $this->resetDB();
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function createAssetFamily(string $rawIdentifier, TransformationCollection $transformationCollection): void
    {
        $imageInfo = new FileInfo();
        $imageInfo
            ->setOriginalFilename(sprintf('image_%s', $rawIdentifier))
            ->setKey(sprintf('test/image_%s.jpg', $rawIdentifier));

        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString($rawIdentifier),
            ['en_US' => $rawIdentifier],
            Image::fromFileInfo($imageInfo),
            RuleTemplateCollection::empty()
        );
        $assetFamily = $assetFamily->withTransformationCollection($transformationCollection);

        $this->assetFamilyRepository->create($assetFamily);
    }

    private function assertCollectionEqualsCollection(
        TransformationCollection $expected,
        TransformationCollection $actual
    ): void {
        Assert::assertSame($expected->getIterator()->count(), $actual->getIterator()->count());
        foreach ($expected as $expectedTransformation) {
            $actualTransformation = $actual->getByTarget($expectedTransformation->getTarget());
            Assert::assertNotNull($actualTransformation);
            Assert::assertTrue($expectedTransformation->equals($actualTransformation));
        }
    }
}

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

namespace Akeneo\AssetManager\Integration\Persistence\InMemory;

use Akeneo\AssetManager\Common\Fake\Connector\InMemoryFindTransformationAssetsByIdentifiers;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationAsset;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class InMemoryFindTransformationAssetsByIdentifiersTest extends TestCase
{
    private InMemoryFindTransformationAssetsByIdentifiers $findTransformationAssetsByIdentifiers;

    public function setUp(): void
    {
        parent::setUp();

        $this->findTransformationAssetsByIdentifiers = new InMemoryFindTransformationAssetsByIdentifiers();
    }

    /**
     * @test
     */
    public function it_finds_transformation_assets_for_a_given_list_of_identifiers()
    {
        $kartellAsset = new TransformationAsset(
            AssetIdentifier::fromString('brand_kartell_fingerprint'),
            AssetCode::fromString('kartell'),
            AssetFamilyIdentifier::fromString('brand'),
            []
        );
        $lexonAsset = new TransformationAsset(
            AssetIdentifier::fromString('brand_lexon_fingerprint'),
            AssetCode::fromString('lexon'),
            AssetFamilyIdentifier::fromString('brand'),
            []
        );
        $alessiAsset = new TransformationAsset(
            AssetIdentifier::fromString('brand_alessi_fingerprint'),
            AssetCode::fromString('alessi'),
            AssetFamilyIdentifier::fromString('brand'),
            []
        );
        $this->findTransformationAssetsByIdentifiers->save($kartellAsset);
        $this->findTransformationAssetsByIdentifiers->save($lexonAsset);
        $this->findTransformationAssetsByIdentifiers->save($alessiAsset);

        $result = $this->findTransformationAssetsByIdentifiers->find(['brand_lexon_fingerprint', 'brand_alessi_fingerprint']);
        $this->assertSameTransformationAssets($result, [$lexonAsset, $alessiAsset]);
    }

    /**
     * @param TransformationAsset[] $results
     * @param TransformationAsset[] $expecteds
     */
    private function assertSameTransformationAssets(array $results, array $expecteds)
    {
        Assert::same(count($results), count($expecteds));
        foreach ($expecteds as $expected) {
            $result = $results[(string) $expected->getIdentifier()];

            Assert::true($expected->getIdentifier()->equals($result->getIdentifier()));
            Assert::true($expected->getAssetFamilyIdentifier()->equals($result->getAssetFamilyIdentifier()));
            Assert::true($expected->getCode()->equals($result->getCode()));
            Assert::eq($expected->getRawValueCollection(), $result->getRawValueCollection());
        }
    }
}

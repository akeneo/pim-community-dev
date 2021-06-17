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

use Akeneo\AssetManager\Common\Fake\Connector\InMemoryFindConnectorAssetByAssetFamilyAndCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\Connector\ConnectorAsset;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class InMemoryFindConnectorAssetTest extends TestCase
{
    private InMemoryFindConnectorAssetByAssetFamilyAndCode $query;

    public function setUp(): void
    {
        $this->query = new InMemoryFindConnectorAssetByAssetFamilyAndCode();
    }

    /**
     * @test
     */
    public function it_returns_null_when_finding_a_non_existent_asset()
    {
        $result = $this->query->find(
            AssetFamilyIdentifier::fromString('asset_family'),
            AssetCode::fromString('non_existent_asset_code')
        );

        Assert::assertNull($result);
    }

    /**
     * @test
     */
    public function it_returns_the_asset_when_finding_an_existent_asset()
    {
        $asset = new ConnectorAsset(
            AssetCode::fromString('asset_code'),
            [],
            new \DateTimeImmutable('@0'),
            new \DateTimeImmutable('@3600'),
        );
        $this->query->save(
            AssetFamilyIdentifier::fromString('asset_family'),
            AssetCode::fromString('asset_code'),
            $asset
        );

        $result = $this->query->find(
            AssetFamilyIdentifier::fromString('asset_family'),
            AssetCode::fromString('asset_code')
        );

        Assert::assertNotNull($result);
        Assert::assertEquals(
            $asset->normalize(),
            $result->normalize()
        );
    }
}

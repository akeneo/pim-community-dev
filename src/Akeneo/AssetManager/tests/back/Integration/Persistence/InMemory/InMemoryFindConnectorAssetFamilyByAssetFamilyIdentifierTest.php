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

use Akeneo\AssetManager\Common\Fake\Connector\InMemoryFindConnectorAssetFamilyByAssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NullNamingConvention;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorAssetFamily;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorTransformationCollection;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class InMemoryFindConnectorAssetFamilyByAssetFamilyIdentifierTest extends TestCase
{
    private InMemoryFindConnectorAssetFamilyByAssetFamilyIdentifier $query;

    public function setUp(): void
    {
        $this->query = new InMemoryFindConnectorAssetFamilyByAssetFamilyIdentifier();
    }

    /**
     * @test
     */
    public function it_returns_null_when_finding_a_non_existent_asset_family()
    {
        $result = $this->query->find(
            AssetFamilyIdentifier::fromString('non_existent_asset_family_identifier')
        );

        Assert::assertNull($result);
    }

    /**
     * @test
     */
    public function it_returns_the_asset_family_when_finding_an_existing_asset_family()
    {
        $assetFamily = new ConnectorAssetFamily(
            AssetFamilyIdentifier::fromString('asset_family_identifier'),
            LabelCollection::fromArray([]),
            Image::createEmpty(),
            [],
            new ConnectorTransformationCollection([]),
            new NullNamingConvention(),
            null
        );

        $this->query->save(
            AssetFamilyIdentifier::fromString('asset_family_identifier'),
            $assetFamily
        );

        $result = $this->query->find(
            AssetFamilyIdentifier::fromString('asset_family_identifier')
        );

        Assert::assertNotNull($result);
        Assert::assertEquals(
            $assetFamily->normalize(),
            $result->normalize()
        );
    }
}

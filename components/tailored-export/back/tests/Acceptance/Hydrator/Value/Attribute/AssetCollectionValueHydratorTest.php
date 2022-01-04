<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\Hydrator\Value\Attribute;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\Pim\Enrichment\AssetManager\Component\Value\AssetCollectionValue as AssetManagerAssetCollectionValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\AssetCollectionValue;

class AssetCollectionValueHydratorTest extends AbstractAttributeValueHydratorTest
{
    /**
     * @test
     */
    public function it_hydrates_an_asset_collection_value_from_product_value(): void
    {
        $expectedValue = new AssetCollectionValue(
            ['an_asset_code', 'another_asset_code'],
            'product_identifier',
            null,
            null,
        );

        $productValue = AssetManagerAssetCollectionValue::value(
            'an_asset_collection',
            [
                AssetCode::fromString('an_asset_code'),
                AssetCode::fromString('another_asset_code'),
            ],
        );

        $this->assertHydratedValueEquals($expectedValue, $productValue);
    }

    /**
     * @test
     */
    public function it_hydrates_an_asset_collection_value_from_localizable_and_scopable_product_value(): void
    {
        $expectedValue = new AssetCollectionValue(
            ['an_asset_code', 'another_asset_code'],
            'product_identifier',
            'ecommerce',
            'en_US',
        );

        $productValue = AssetManagerAssetCollectionValue::scopableLocalizableValue(
            'asset_collection_attribute_code',
            [
                AssetCode::fromString('an_asset_code'),
                AssetCode::fromString('another_asset_code'),
            ],
            'ecommerce',
            'en_US',
        );

        $this->assertHydratedValueEquals($expectedValue, $productValue);
    }

    protected function getAttributeType(): string
    {
        return 'pim_catalog_asset_collection';
    }
}

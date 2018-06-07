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

namespace spec\PimEnterprise\Component\ProductAsset\Comparator\Attribute;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Comparator\ComparatorInterface;
use PimEnterprise\Bundle\ProductAssetBundle\AttributeType\AttributeTypes as EnterpriseAttributeTypes;
use PimEnterprise\Component\ProductAsset\Comparator\Attribute\AssetCollectionComparator;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AssetCollectionComparatorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['pim_assets_collection']);
    }

    function it_is_an_asset_comparator()
    {
        $this->shouldHaveType(AssetCollectionComparator::class);
        $this->shouldImplement(ComparatorInterface::class);
    }

    function it_supports_asset_collection_attributes()
    {
        $this->supports(EnterpriseAttributeTypes::ASSETS_COLLECTION)->shouldReturn(true);
        $this->supports(AttributeTypes::BACKEND_TYPE_BOOLEAN)->shouldReturn(false);
    }

    function it_returns_null_if_compared_data_are_identical()
    {
        $this->compare(
            ['data' => ['foo', 'bar']],
            ['data' => ['foo', 'bar']]
        )->shouldReturn(null);
    }

    function it_returns_the_original_data_if_compared_data_are_different()
    {
        $this->compare(
            ['data' => ['foo', 'bar']],
            ['data' => ['bar', 'baz']]
        )->shouldReturn(['data' => ['foo', 'bar']]);
    }

    function it_returns_the_original_data_if_compared_data_are_the_same_but_in_different_order()
    {
        $this->compare(
            ['data' => ['foo', 'bar']],
            ['data' => ['bar', 'foo']]
        )->shouldReturn(['data' => ['foo', 'bar']]);
    }
}

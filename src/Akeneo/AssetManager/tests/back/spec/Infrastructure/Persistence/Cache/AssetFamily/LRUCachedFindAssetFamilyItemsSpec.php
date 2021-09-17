<?php
declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Cache\AssetFamily;

use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyItemsInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Cache\AssetFamily\LRUCachedFindAssetFamilyItems;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LRUCachedFindAssetFamilyItemsSpec extends ObjectBehavior
{
    function let(FindAssetFamilyItemsInterface $findAssetFamilyItems)
    {
        $this->beConstructedWith($findAssetFamilyItems);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(FindAssetFamilyItemsInterface::class);
        $this->shouldHaveType(LRUCachedFindAssetFamilyItems::class);
    }

    function it_caches_asset_family_items(FindAssetFamilyItemsInterface $findAssetFamilyItems)
    {
        $findAssetFamilyItems->find()->shouldBeCalledOnce();

        $severalCallsToFindAssetFamilyItems = 3;
        for ($i = 0; $i < $severalCallsToFindAssetFamilyItems; ++$i) {
            $this->find();
        }
    }
}

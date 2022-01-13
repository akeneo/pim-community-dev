<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Query\AssetManager;

use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\GetAssetMainMediaDataInterface;
use Akeneo\Platform\TailoredExport\Infrastructure\Query\AssetManager\FindAssetMainMediaData;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @require Akeneo\AssetManager\Infrastructure\PublicApi\Platform\GetAssetMainMediaDataInterface
 */
class FindAssetMainMediaDataSpec extends ObjectBehavior
{
    public function let(
        GetAssetMainMediaDataInterface $getMainMainMediaData
    ): void {
        $this->beConstructedWith($getMainMainMediaData);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(FindAssetMainMediaData::class);
    }

    public function it_finds_asset_main_media_data(
        GetAssetMainMediaDataInterface $getMainMainMediaData
    ): void {
        $assetFamilyCode = 'images';
        $assetCodes = ['atmosphere1', 'atmosphere2', 'unknown'];
        $channel = 'ecommerce';
        $locale = null;
        $expectedReturn = [
            'http//test.fr',
            'http://test.com'
        ];

        $getMainMainMediaData->forAssetFamilyAndAssetCodes($assetFamilyCode, $assetCodes, $channel, $locale)->willReturn($expectedReturn);
        $this->forAssetFamilyAndAssetCodes($assetFamilyCode, $assetCodes, $channel, $locale)->shouldBeLike($expectedReturn);
    }
}

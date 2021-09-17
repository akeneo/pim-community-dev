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

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Query\AssetManager;

use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\GetAssetMainMediaValuesInterface;
use Akeneo\Platform\TailoredExport\Infrastructure\Query\AssetManager\FindMediaLinks;
use PhpSpec\ObjectBehavior;

class FindMediaLinksSpec extends ObjectBehavior
{
    public function let(
        GetAssetMainMediaValuesInterface $getAssetMainMediaValues
    ): void {
        $this->beConstructedWith($getAssetMainMediaValues);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(FindMediaLinks::class);
    }

    public function it_finds_media_links(
        GetAssetMainMediaValuesInterface $getAssetMainMediaValues
    ): void {
        $assetFamilyCode = 'images';
        $assetCodes = ['atmosphere1', 'atmosphere2', 'unknown'];
        $expectedMainMediaValues = [
            'atmosphere1' => [[
                "data" => "http://atmos1.fr",
                "locale" => null,
                "channel" => null,
            ]],
            'atmosphere2' => [[
                "data" => "http://atmos2.fr",
                "locale" => null,
                "channel" => null,
            ]]
        ];

        $getAssetMainMediaValues->forAssetFamilyAndAssetCodes($assetFamilyCode, $assetCodes)
            ->willReturn($expectedMainMediaValues);

        $this->forScopedAndLocalizedAssetFamilyAndAssetCodes($assetFamilyCode, $assetCodes, null, null)
            ->shouldReturn(["http://atmos1.fr", "http://atmos2.fr"]);
    }

    public function it_finds_scoped_media_links(
        GetAssetMainMediaValuesInterface $getAssetMainMediaValues
    ): void {
        $assetFamilyCode = 'images';
        $assetCodes = ['atmosphere1', 'atmosphere2', 'unknown'];
        $expectedMainMediaValues = [
            'atmosphere1' => [[
                "data" => "http://atmos1.fr",
                "locale" => null,
                "channel" => 'ecommerce',
            ]],
            'atmosphere2' => [[
                "data" => "http://atmos2.fr",
                "locale" => null,
                "channel" => 'print',
            ]]
        ];

        $getAssetMainMediaValues->forAssetFamilyAndAssetCodes($assetFamilyCode, $assetCodes)
            ->willReturn($expectedMainMediaValues);

        $this->forScopedAndLocalizedAssetFamilyAndAssetCodes($assetFamilyCode, $assetCodes, 'print', null)
            ->shouldReturn(["http://atmos2.fr"]);
    }

    public function it_finds_localized_media_links(
        GetAssetMainMediaValuesInterface $getAssetMainMediaValues
    ): void {
        $assetFamilyCode = 'images';
        $assetCodes = ['atmosphere1', 'atmosphere2', 'unknown'];
        $expectedMainMediaValues = [
            'atmosphere1' => [[
                "data" => "http://atmos1.com",
                "locale" => 'en_US',
                "channel" => null,
            ]],
            'atmosphere2' => [[
                "data" => "http://atmos2.fr",
                "locale" => 'fr_FR',
                "channel" => null,
            ]]
        ];

        $getAssetMainMediaValues->forAssetFamilyAndAssetCodes($assetFamilyCode, $assetCodes)
            ->willReturn($expectedMainMediaValues);

        $this->forScopedAndLocalizedAssetFamilyAndAssetCodes($assetFamilyCode, $assetCodes, null, 'en_US')
            ->shouldReturn(["http://atmos1.com"]);
    }

    public function it_finds_scoped_localized_media_links(
        GetAssetMainMediaValuesInterface $getAssetMainMediaValues
    ): void {
        $assetFamilyCode = 'images';
        $assetCodes = ['atmosphere1', 'atmosphere2', 'unknown'];
        $expectedMainMediaValues = [
            'atmosphere1' => [[
                "data" => "http://atmos1.com",
                "locale" => 'en_US',
                "channel" => 'ecommerce',
            ]],
            'atmosphere2' => [[
                "data" => "http://atmos2.fr",
                "locale" => 'fr_FR',
                "channel" => 'ecommerce',
            ]]
        ];

        $getAssetMainMediaValues->forAssetFamilyAndAssetCodes($assetFamilyCode, $assetCodes)
            ->willReturn($expectedMainMediaValues);

        $this->forScopedAndLocalizedAssetFamilyAndAssetCodes($assetFamilyCode, $assetCodes, 'ecommerce', 'en_US')
            ->shouldReturn(["http://atmos1.com"]);
    }
}

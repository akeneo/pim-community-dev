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

use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\FindAssetLabelTranslationInterface;
use Akeneo\Platform\TailoredExport\Infrastructure\Query\AssetManager\FindAssetLabels;
use PhpSpec\ObjectBehavior;

class FindAssetLabelsSpec extends ObjectBehavior
{
    public function let(
        FindAssetLabelTranslationInterface $findAssetLabelTranslation
    ): void {
        $this->beConstructedWith($findAssetLabelTranslation);
    }

    public function it_is_initializable(): void
    {
        $this->beAnInstanceOf(FindAssetLabels::class);
    }

    public function it_finds_asset_labels(
        FindAssetLabelTranslationInterface $findAssetLabelTranslation
    ): void {
        $assetFamilyCode = 'images';
        $assetCodes = ['atmosphere1', 'atmosphere2'];
        $localeCode = 'fr_FR';

        $expectedLabel = ['atmosphere1' => 'Atmosphere Main', 'atmosphere2' => 'Atmosphere Secondary'];
        $findAssetLabelTranslation->byFamilyCodeAndAssetCodes($assetFamilyCode, $assetCodes, $localeCode)
            ->willReturn($expectedLabel);

        $this->byAssetFamilyCodeAndAssetCodes($assetFamilyCode, $assetCodes, $localeCode)->shouldReturn($expectedLabel);
    }
}

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

namespace spec\Akeneo\AssetManager\Application\Asset\MassDeleteAssets;

use Akeneo\AssetManager\Application\Asset\MassDeleteAssets\MassDeleteAssetsCommand;
use Akeneo\AssetManager\Application\Asset\MassDeleteAssets\MassDeleteAssetsLauncherInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use PhpSpec\ObjectBehavior;

class MassDeleteAssetsHandlerSpec extends ObjectBehavior
{
    function let(MassDeleteAssetsLauncherInterface $massDeleteAssetsLauncher)
    {
        $this->beConstructedWith($massDeleteAssetsLauncher);
    }

    function is_launch_a_job(MassDeleteAssetsLauncherInterface $massDeleteAssetsLauncher)
    {
        $normalizedQuery = [
            "page" => 0,
            "size" => 50,
            "locale" => "en_US",
            "channel" => "ecommerce",
            "filters" => [
                [
                    "field" => "asset_family",
                    "value" => "packshot",
                    "context" => [],
                    "operator" => "="
                ],
                [
                    "field" => "code",
                    "value" => ["packshot_1"],
                    "context" => [],
                    "operator" => "IN"
                ],
            ]
        ];

        $editAssetCommand = new MassDeleteAssetsCommand('packshot', $normalizedQuery);

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $query = AssetQuery::createFromNormalized($normalizedQuery);

        $massDeleteAssetsLauncher
            ->launchForAssetFamilyAndQuery($assetFamilyIdentifier, $query)
            ->shouldBeCalled();

        $this->__invoke($editAssetCommand);
    }
}

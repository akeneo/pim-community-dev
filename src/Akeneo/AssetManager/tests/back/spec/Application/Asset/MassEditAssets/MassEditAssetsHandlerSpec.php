<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\AssetManager\Application\Asset\MassEditAssets;

use Akeneo\AssetManager\Application\Asset\MassEditAssets\MassEditAssetsCommand;
use Akeneo\AssetManager\Application\Asset\MassEditAssets\MassEditAssetsLauncherInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use PhpSpec\ObjectBehavior;

class MassEditAssetsHandlerSpec extends ObjectBehavior
{
    function let(MassEditAssetsLauncherInterface $massDeleteAssetsLauncher)
    {
        $this->beConstructedWith($massDeleteAssetsLauncher);
    }

    function is_launch_a_job(MassEditAssetsLauncherInterface $massEditAssetsLauncher)
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

        $editAssetCommand = new MassEditAssetsCommand('packshot', $normalizedQuery, []);

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $query = AssetQuery::createFromNormalized($normalizedQuery);

        $massEditAssetsLauncher
            ->launchForAssetFamily($assetFamilyIdentifier, $query, [])
            ->shouldBeCalled();

        $this->__invoke($editAssetCommand);
    }
}

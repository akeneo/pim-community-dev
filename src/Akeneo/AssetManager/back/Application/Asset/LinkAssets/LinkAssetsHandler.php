<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\LinkAssets;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class LinkAssetsHandler
{
    /** @var ProductLinkRuleLauncherInterface */
    private $productLinkRuleLauncher;

    public function __construct(ProductLinkRuleLauncherInterface $productLinkRuleLauncher)
    {
        $this->productLinkRuleLauncher = $productLinkRuleLauncher;
    }

    public function handle(LinkMultipleAssetsCommand $command): void
    {
        $assetCodesByFamily = [];

        foreach ($command->linkAssetCommands as $linkAssetCommand) {
            $familyIdentifier = $linkAssetCommand->assetFamilyIdentifier;
            $assetCodesByFamily[$familyIdentifier][] = AssetCode::fromString($linkAssetCommand->assetCode);
        }

        foreach ($assetCodesByFamily as $familyIdentifier => $assetCodes) {
            $this->productLinkRuleLauncher->launch(
                AssetFamilyIdentifier::fromString($familyIdentifier),
                $assetCodes
            );
        }
    }
}

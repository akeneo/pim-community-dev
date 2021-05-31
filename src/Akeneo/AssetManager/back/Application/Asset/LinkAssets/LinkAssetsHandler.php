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
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class LinkAssetsHandler
{
    /** * @var AssetFamilyRepositoryInterface */
    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private ProductLinkRuleLauncherInterface $productLinkRuleLauncher;

    public function __construct(AssetFamilyRepositoryInterface $assetFamilyRepository, ProductLinkRuleLauncherInterface $productLinkRuleLauncher)
    {
        $this->productLinkRuleLauncher = $productLinkRuleLauncher;
        $this->assetFamilyRepository = $assetFamilyRepository;
    }

    public function handle(LinkMultipleAssetsCommand $command): void
    {
        $assetCodesByFamily = [];

        foreach ($command->linkAssetCommands as $linkAssetCommand) {
            $familyIdentifier = $linkAssetCommand->assetFamilyIdentifier;
            $assetCodesByFamily[$familyIdentifier][] = AssetCode::fromString($linkAssetCommand->assetCode);
        }

        foreach ($assetCodesByFamily as $familyIdentifier => $assetCodes) {
            if (!$this->assetFamilyHasProductLinkRule($familyIdentifier)) {
                continue;
            }
            $this->productLinkRuleLauncher->launchForAssetFamilyAndAssetCodes(
                AssetFamilyIdentifier::fromString($familyIdentifier),
                $assetCodes
            );
        }
    }

    private function assetFamilyHasProductLinkRule(string $familyIdentifier)
    {
        $assetFamily = $this->assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString($familyIdentifier));

        return !$assetFamily->getRuleTemplateCollection()->isEmpty();
    }
}

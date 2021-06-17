<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\LinkAssets;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class LinkAllAssetFamilyAssetsHandler
{
    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private ProductLinkRuleLauncherInterface $productLinkRuleLauncher;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        ProductLinkRuleLauncherInterface $productLinkRuleLauncher
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->productLinkRuleLauncher = $productLinkRuleLauncher;
    }

    public function __invoke(LinkAllAssetFamilyAssetsCommand $command): void
    {
        $assetFamily = $this->assetFamilyRepository->getByIdentifier(
            AssetFamilyIdentifier::fromString($command->assetFamilyIdentifier)
        );
        if (!$assetFamily->getRuleTemplateCollection()->isEmpty()) {
            $this->productLinkRuleLauncher->launchForAllAssetFamilyAssets(
                AssetFamilyIdentifier::fromString($command->assetFamilyIdentifier)
            );
        }
    }
}

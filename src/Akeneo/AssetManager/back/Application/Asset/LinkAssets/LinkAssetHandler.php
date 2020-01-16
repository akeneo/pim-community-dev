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
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class LinkAssetHandler
{
    /** @var ProductLinkRuleLauncherInterface */
    private $productLinkRuleLauncher;

    public function __construct(ProductLinkRuleLauncherInterface $productLinkRuleLauncher)
    {
        $this->productLinkRuleLauncher = $productLinkRuleLauncher;
    }

    public function __invoke(LinkAssetCommand $command): void
    {
        $this->productLinkRuleLauncher->launch(
            AssetFamilyIdentifier::fromString($command->assetFamilyIdentifier),
            [AssetCode::fromString($command->assetCode)]
        );
    }
}

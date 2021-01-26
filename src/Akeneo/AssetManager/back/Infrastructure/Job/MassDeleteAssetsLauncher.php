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

namespace Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Application\Asset\MassDeleteAssets\MassDeleteAssetsLauncherInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;

/** *
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class MassDeleteAssetsLauncher implements MassDeleteAssetsLauncherInterface
{
    private PublishJobToQueue $publishJobToQueue;

    public function __construct(PublishJobToQueue $publishJobToQueue)
    {
        $this->publishJobToQueue = $publishJobToQueue;
    }

    public function launchForAssetFamilyAndQuery(AssetFamilyIdentifier $assetFamilyIdentifier, AssetQuery $assetQuery): void
    {
        $config = [
            'asset_family_identifier' => (string) $assetFamilyIdentifier,
            'query' => $assetQuery->normalize(),
        ];

        $this->publishJobToQueue->publish('asset_manager_mass_delete_assets', $config);
    }
}

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

namespace Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationLauncherInterface;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;
use Webmozart\Assert\Assert;

class ComputeTransformationLauncher implements ComputeTransformationLauncherInterface
{
    /** @var PublishJobToQueue */
    private $publishJobToQueue;

    public function __construct(PublishJobToQueue $publishJobToQueue)
    {
        $this->publishJobToQueue = $publishJobToQueue;
    }

    /**
     * @param AssetIdentifier[] $assetIdentifiers
     */
    public function launch(array $assetIdentifiers): void
    {
        Assert::allIsInstanceOf($assetIdentifiers, AssetIdentifier::class);

        $config = [
            'asset_identifiers' => array_map(function (AssetIdentifier $assetIdentifier) {
                return (string) $assetIdentifier;
            }, $assetIdentifiers),
        ];

        $this->publishJobToQueue->publish(
            'asset_manager_compute_transformations',
            $config
        );
    }
}

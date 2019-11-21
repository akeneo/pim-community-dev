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

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;
use Webmozart\Assert\Assert;

class ComputeTransformationLauncher
{
    /** @var PublishJobToQueue */
    private $publishJobToQueue;

    public function __construct(PublishJobToQueue $publishJobToQueue)
    {
        $this->publishJobToQueue = $publishJobToQueue;
    }

    /**
     * @param AssetCode[] $assetCodes
     */
    public function launch(array $assetCodes): void
    {
        Assert::allIsInstanceOf($assetCodes, AssetCode::class);

        $config = [
            'asset_codes' => array_map(function (AssetCode $assetCode) {
                return (string) $assetCode;
            }, $assetCodes),
        ];

        $this->publishJobToQueue->publish(
            'asset_manager_compute_transformations',
            $config
        );
    }
}

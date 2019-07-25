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

use Akeneo\AssetManager\Application\Asset\ExecuteRuleTemplates\AsynchronousProductLinkRuleLauncherInterface;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;

/**
 * Implementation of the AsynchronousProductLinkRuleLauncherInterface using Akeneo PIM Job Queue system.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class AsynchronousProductLinkRuleLauncher implements AsynchronousProductLinkRuleLauncherInterface
{
    /** @var PublishJobToQueue */
    private $publishJobToQueue;

    public function __construct(PublishJobToQueue $publishJobToQueue)
    {
        $this->publishJobToQueue = $publishJobToQueue;
    }

    public function launch(AssetFamilyIdentifier $assetFamilyIdentifier, AssetCode $assetCode): void
    {
        $config = [
            'asset_family_identifier' => (string) $assetFamilyIdentifier,
            'asset_code' => (string) $assetCode,
        ];

        $this->publishJobToQueue->publish(
            'asset_manager_link_assets_to_products',
            $config
        );
    }
}

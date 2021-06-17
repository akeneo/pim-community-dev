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

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\Asset;

use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkAssetCommand;
use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkAssetsHandler;
use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkMultipleAssetsCommand;

/**
 * This stateful class keeps track of all the assets that have been created via the API.
 *
 * Right before the end of the synchronization of the ERP and the PIM, it starts the process of linking to products all
 * the created assets during the synchronization.
 *
 * (We do this task on the symfony "kernel.terminate")
 *
 * @see https://symfony.com/doc/current/components/http_kernel.html#the-kernel-terminate-event
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class BatchAssetsToLink
{
    private LinkAssetsHandler $linkAssetsHandler;

    public LinkMultipleAssetsCommand $linkMultipleAssetsCommand;

    public function __construct(LinkAssetsHandler $linkAssetsHandler)
    {
        $this->linkAssetsHandler = $linkAssetsHandler;
        $this->linkMultipleAssetsCommand = new LinkMultipleAssetsCommand();
    }

    public function add(string $assetFamilyIdentifier, string $assetCode): void
    {
        $linkAssetCommand = new LinkAssetCommand();
        $linkAssetCommand->assetCode = $assetCode;
        $linkAssetCommand->assetFamilyIdentifier = $assetFamilyIdentifier;

        $this->linkMultipleAssetsCommand->linkAssetCommands[] = $linkAssetCommand;
    }

    public function runBatch(): void
    {
        if (empty($this->linkMultipleAssetsCommand->linkAssetCommands)) {
            return;
        }

        $this->linkAssetsHandler->handle($this->linkMultipleAssetsCommand);
    }

    public function onKernelTerminate(): void
    {
        $this->runBatch();
    }
}

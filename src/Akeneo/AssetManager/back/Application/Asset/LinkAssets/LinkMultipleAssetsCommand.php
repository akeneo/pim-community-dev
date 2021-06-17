<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\LinkAssets;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class LinkMultipleAssetsCommand
{
    /** @var LinkAssetCommand[] */
    public array $linkAssetCommands = [];
}

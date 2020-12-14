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

namespace Akeneo\AssetManager\Application\Asset\CreateAndEditAsset;

use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;

final class CreateAndEditAssetCommand
{
    public ?CreateAssetCommand $createAssetCommand;
    public EditAssetCommand $editAssetCommand;

    public function __construct(?CreateAssetCommand $createAssetCommand, EditAssetCommand $editAssetCommand)
    {
        $this->createAssetCommand = $createAssetCommand;
        $this->editAssetCommand = $editAssetCommand;
    }
}

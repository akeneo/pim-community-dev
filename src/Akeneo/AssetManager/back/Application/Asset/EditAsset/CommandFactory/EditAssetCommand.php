<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory;

/**
 * It represents the intent to edit a asset
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditAssetCommand
{
    public string $assetFamilyIdentifier;
    public string $code;

    /** @var AbstractEditValueCommand[] */
    public array $editAssetValueCommands = [];

    public function __construct(
        string $assetFamilyIdentifier,
        string $code,
        array $editAssetValueCommands
    ) {
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
        $this->code = $code;
        $this->editAssetValueCommands = $editAssetValueCommands;
    }
}

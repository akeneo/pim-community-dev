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
    /** @var string */
    public $assetFamilyIdentifier;

    /** @var string */
    public $code;

    /** @var array */
    public $labels = [];

    /** @var string[]|null */
    public $image;

    /** @var array */
    public $editAssetValueCommands = [];

    public function __construct(
        string $assetFamilyIdentifier,
        string $code,
        array $labels,
        ?array $image,
        array $editAssetValueCommands
    ) {
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
        $this->code = $code;
        $this->labels = $labels;
        $this->image = $image;
        $this->editAssetValueCommands = $editAssetValueCommands;
    }
}

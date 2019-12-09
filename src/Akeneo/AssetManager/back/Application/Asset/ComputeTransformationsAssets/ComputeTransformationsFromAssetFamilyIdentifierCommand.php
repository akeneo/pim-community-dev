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

namespace Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets;

class ComputeTransformationsFromAssetFamilyIdentifierCommand
{
    /** @var string */
    private $assetFamilyIdentifier;

    public function __construct(string $assetIdentifiers)
    {
        $this->assetFamilyIdentifier = $assetIdentifiers;
    }

    public function getAssetFamilyIdentifier(): string
    {
        return $this->assetFamilyIdentifier;
    }
}

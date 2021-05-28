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

namespace Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

/**
 * Read model used for the transformations.
 */
class TransformationAsset
{
    private AssetIdentifier $identifier;

    private AssetCode $code;

    private AssetFamilyIdentifier $assetFamilyIdentifier;

    private array $rawValueCollection;

    public function __construct(
        AssetIdentifier $identifier,
        AssetCode $code,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        array $rawValueCollection
    ) {
        $this->identifier = $identifier;
        $this->code = $code;
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
        $this->rawValueCollection = $rawValueCollection;
    }

    public function getIdentifier(): AssetIdentifier
    {
        return $this->identifier;
    }

    public function getCode(): AssetCode
    {
        return $this->code;
    }

    public function getAssetFamilyIdentifier(): AssetFamilyIdentifier
    {
        return $this->assetFamilyIdentifier;
    }

    public function getRawValueCollection(): array
    {
        return $this->rawValueCollection;
    }
}

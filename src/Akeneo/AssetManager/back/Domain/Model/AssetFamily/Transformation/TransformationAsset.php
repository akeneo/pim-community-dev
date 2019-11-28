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
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

/**
 * Domain model used for the transformations.
 */
class TransformationAsset
{
    /** @var AssetIdentifier */
    private $identifier;

    /** @var AssetCode */
    private $code;

    /** @var AssetFamilyIdentifier */
    private $assetFamilyIdentifier;

    /** @var ValueCollection */
    private $valueCollection;

    public function __construct(
        AssetIdentifier $identifier,
        AssetCode $code,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        ValueCollection $valueCollection
    ) {
        $this->identifier = $identifier;
        $this->code = $code;
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
        $this->valueCollection = $valueCollection;
    }
}

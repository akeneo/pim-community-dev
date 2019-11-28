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

/**
 * Domain model used for the transformations.
 */
class TransformationAsset
{
    /** @var string */
    private $identifier;

    /** @var string */
    private $code;

    /** @var string */
    private $assetFamilyIdentifier;

    /** @var array */
    private $valueCollection;

    public function __construct(
        string $identifier,
        string $code,
        string $assetFamilyIdentifier,
        array $valueCollection
    ) {
        $this->identifier = $identifier;
        $this->code = $code;
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
        $this->valueCollection = $valueCollection;
    }
}

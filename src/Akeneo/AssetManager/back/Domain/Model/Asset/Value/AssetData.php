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

namespace Akeneo\AssetManager\Domain\Model\Asset\Value;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Webmozart\Assert\Assert;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class AssetData implements ValueDataInterface
{
    /** @var string */
    private $assetCode;

    private function __construct(string $assetCode)
    {
        Assert::stringNotEmpty($assetCode, 'Asset code should be a non empty string');

        $this->assetCode = $assetCode;
    }

    /**
     * @return string
     */
    public function normalize()
    {
        return $this->assetCode;
    }

    public static function createFromNormalize($normalizedData): ValueDataInterface
    {
        Assert::string($normalizedData, 'Normalized data should be a string');

        return new self($normalizedData);
    }

    public static function fromAssetCode(AssetCode $assetCode): AssetData
    {
        return new self((string) $assetCode);
    }
}

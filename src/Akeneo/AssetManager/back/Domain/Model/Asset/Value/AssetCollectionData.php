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
class AssetCollectionData implements ValueDataInterface
{
    /** @var string[] */
    private $assetCodes;

    private function __construct(array $assetCodes)
    {
        Assert::notEmpty($assetCodes, 'Asset codes should be a non empty array');

        $this->assetCodes = $assetCodes;
    }

    /**
     * @return string
     */
    public function normalize()
    {
        return $this->assetCodes;
    }

    public static function createFromNormalize($normalizedData): ValueDataInterface
    {
        Assert::allString($normalizedData, 'Each asset codes should be a string');

        return new self($normalizedData);
    }

    public static function fromAssetCodes(array $assetCodes): AssetCollectionData
    {
        Assert::allIsInstanceOf(
            $assetCodes,
            AssetCode::class,
            sprintf('Each asset codes should be an instance of "%s"', AssetCode::class)
        );

        $assetCodesString = array_map('strval', $assetCodes);

        return new self($assetCodesString);
    }
}

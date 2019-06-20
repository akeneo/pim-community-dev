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

namespace Akeneo\AssetManager\Domain\Model\Asset;

use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class Asset
{
    /** @var AssetIdentifier */
    private $identifier;

    /** @var AssetCode */
    private $code;

    /** @var AssetFamily */
    private $assetFamilyIdentifier;

    /** @var ValueCollection */
    private $valueCollection;

    private function __construct(
        AssetIdentifier $identifier,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetCode $code,
        ValueCollection $valueCollection
    ) {
        $this->identifier = $identifier;
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
        $this->code = $code;
        $this->valueCollection = $valueCollection;
    }

    public static function create(
        AssetIdentifier $identifier,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetCode $code,
        ValueCollection $valueCollection
    ): self {
        return new self($identifier, $assetFamilyIdentifier, $code, $valueCollection);
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

    public function equals(Asset $asset): bool
    {
        return $this->identifier->equals($asset->identifier);
    }

    public function getValues(): ValueCollection
    {
        return $this->valueCollection;
    }

    public function setValue(Value $value): void
    {
        $this->valueCollection = $this->valueCollection->setValue($value);
    }

    public function findValue(ValueKey $valueKey): ?Value
    {
        return $this->valueCollection->findValue($valueKey);
    }

    public function normalize(): array
    {
        return [
            'identifier' => $this->identifier->normalize(),
            'code' => $this->code->normalize(),
            'assetFamilyIdentifier' => $this->assetFamilyIdentifier->normalize(),
            'values' => $this->valueCollection->normalize(),
        ];
    }

    public function filterValues(\Closure $closure): ValueCollection
    {
        return $this->valueCollection->filter($closure);
    }
}

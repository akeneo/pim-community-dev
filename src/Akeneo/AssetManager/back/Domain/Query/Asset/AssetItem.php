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

namespace Akeneo\AssetManager\Domain\Query\Asset;

/**
 * Read model representing a asset within the list.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetItem
{
    private const IDENTIFIER = 'identifier';
    private const ASSET_FAMILY_IDENTIFIER = 'asset_family_identifier';
    private const CODE = 'code';
    private const LABELS = 'labels';
    private const IMAGE = 'image';
    private const VALUES = 'values';
    private const COMPLETENESS = 'completeness';

    public string $identifier;

    public string $assetFamilyIdentifier;

    public string $code;

    public array $labels;

    public array $image;

    /** @var []|null */
    public $values;

    public array $completeness;

    public function normalize(): array
    {
        return [
            self::IDENTIFIER              => $this->identifier,
            self::ASSET_FAMILY_IDENTIFIER => $this->assetFamilyIdentifier,
            self::CODE                    => $this->code,
            self::LABELS                  => $this->labels,
            self::IMAGE                   => $this->image,
            self::VALUES                  => $this->values,
            self::COMPLETENESS            => $this->completeness,
        ];
    }
}

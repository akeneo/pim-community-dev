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

namespace Akeneo\AssetManager\Domain\Query\Attribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeDetails
{
    public const IDENTIFIER = 'identifier';
    public const ASSET_FAMILY_IDENTIFIER = 'asset_family_identifier';
    public const CODE = 'code';
    public const LABELS = 'labels';
    public const IS_REQUIRED = 'is_required';
    public const IS_READ_ONLY = 'is_read_only';
    public const ORDER = 'order';
    public const VALUE_PER_LOCALE = 'value_per_locale';
    public const VALUE_PER_CHANNEL = 'value_per_channel';
    public const TYPE = 'type';

    public string $type;

    public string $identifier;

    public string $assetFamilyIdentifier;

    public string $code;

    public array $labels;

    public int $order;

    public bool $isRequired;

    public bool $isReadOnly;

    public bool $valuePerChannel;

    public bool $valuePerLocale;

    public array $additionalProperties;

    public function normalize(): array
    {
        $commonProperties = [
            self::TYPE => $this->type,
            self::IDENTIFIER => $this->identifier,
            self::ASSET_FAMILY_IDENTIFIER => $this->assetFamilyIdentifier,
            self::CODE => $this->code,
            self::LABELS => $this->labels,
            self::IS_REQUIRED => $this->isRequired,
            self::IS_READ_ONLY => $this->isReadOnly,
            self::ORDER => $this->order,
            self::VALUE_PER_LOCALE => $this->valuePerLocale,
            self::VALUE_PER_CHANNEL => $this->valuePerChannel,
        ];

        return array_merge($commonProperties, $this->additionalProperties);
    }
}

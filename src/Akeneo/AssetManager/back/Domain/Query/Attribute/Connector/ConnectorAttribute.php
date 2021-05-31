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

namespace Akeneo\AssetManager\Domain\Query\Attribute\Connector;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\LabelCollection;

/**
 * @author    Tamara Robichet <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectorAttribute
{
    private const ATTRIBUTE_TYPES = [
        'asset' => 'asset_family_single_link',
        'asset_collection' => 'asset_family_multiple_links',
        'option' => 'single_option',
        'option_collection' => 'multiple_options',
    ];

    private const ATTRIBUTE_NAMES = [
        'max_length' => 'max_characters',
        'regular_expression' => 'validation_regexp',
        'asset_type' => 'asset_family_code',
    ];

    private AttributeCode $code;

    private LabelCollection $labelCollection;

    private string $type;

    private AttributeValuePerLocale $valuePerLocale;

    private AttributeValuePerChannel $valuePerChannel;

    private AttributeIsRequired $isRequired;

    private AttributeIsReadOnly $isReadOnly;

    private array $additionalProperties;

    public function __construct(
        AttributeCode $identifier,
        LabelCollection $labelCollection,
        string $type,
        AttributeValuePerLocale $valuePerLocale,
        AttributeValuePerChannel $valuePerChannel,
        AttributeIsRequired $isRequired,
        AttributeIsReadOnly $isReadOnly,
        array $additionalProperties
    ) {
        $this->code = $identifier;
        $this->labelCollection = $labelCollection;
        $this->type = $type;
        $this->valuePerLocale = $valuePerLocale;
        $this->valuePerChannel = $valuePerChannel;
        $this->isRequired = $isRequired;
        $this->isReadOnly = $isReadOnly;
        $this->additionalProperties = $additionalProperties;
    }

    public function mapAttributeType(string $type)
    {
        return self::ATTRIBUTE_TYPES[$this->type] ?? $type;
    }

    public function mapAttributeName(string $name)
    {
        return self::ATTRIBUTE_NAMES[$name] ?? $name;
    }

    public function normalize(): array
    {
        $normalizedLabels = $this->labelCollection->normalize();

        $commonProperties = [
            'code' => $this->code->__toString(),
            'labels' => empty($normalizedLabels) ? (object) [] : $normalizedLabels,
            'type' => $this->mapAttributeType($this->type),
            'value_per_locale' => $this->valuePerLocale->normalize(),
            'value_per_channel' => $this->valuePerChannel->normalize(),
            'is_required_for_completeness' => $this->isRequired->normalize(),
            'is_read_only' => $this->isReadOnly->normalize(),
        ];

        $additionalProperties = [];

        foreach ($this->additionalProperties as $key => $value) {
            $additionalProperties[$this->mapAttributeName($key)] = $value;
        }

        return array_merge($commonProperties, $additionalProperties);
    }
}

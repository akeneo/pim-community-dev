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

namespace Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;

/**
 * @author    Tamara Robichet <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectorAttribute
{
    private const ATTRIBUTE_TYPES = [
        'record' => 'reference_entity_single_link',
        'record_collection' => 'reference_entity_multiple_links',
        'option' => 'single_option',
        'option_collection' => 'multiple_options'
    ];

    private const ATTRIBUTE_NAMES = [
        'max_length' => 'max_characters',
        'regular_expression' => 'validation_regexp',
        'record_type' => 'reference_entity_code'
    ];

    /** @var AttributeIdentifier */
    private $code;

    /** @var LabelCollection */
    private $labelCollection;

    /** @var string */
    private $type;

    /** @var bool */
    private $localizable;

    /** @var bool */
    private $scopable;

    /** @var bool */
    private $isRequired;

    /** @var array */
    private $additionalProperties;

    public function __construct(
        AttributeCode $identifier,
        LabelCollection $labelCollection,
        string $type,
        AttributeValuePerLocale $localizable,
        AttributeValuePerChannel $scopable,
        AttributeIsRequired $isRequired,
        array $additionalProperties
    ) {
        $this->code = $identifier;
        $this->labelCollection = $labelCollection;
        $this->type = $type;
        $this->localizable = $localizable;
        $this->scopable = $scopable;
        $this->isRequired = $isRequired;
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
        $commonProperties = [
            'code' => $this->code->__toString(),
            'labels' => $this->labelCollection->normalize(),
            'type' => $this->mapAttributeType($this->type),
            'localizable' => $this->localizable->normalize(),
            'scopable' => $this->scopable->normalize(),
            'is_required_for_completeness' => $this->isRequired->normalize()
        ];

        $additionalProperties = [];

        foreach ($this->additionalProperties as $key => $value) {
            $additionalProperties[$this->mapAttributeName($key)] = $value;
        }

        return array_merge($commonProperties, $additionalProperties);
    }
}

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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
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

    /** @var AttributeIdentifier */
    private $identifier;

    /** @var LabelCollection */
    public $labelCollection;

    /** @var string */
    public $type;

    /** @var bool */
    public $localizable;

    /** @var bool */
    public $scopable;

    /** @var bool */
    public $isRequired;

    /** @var array */
    public $additionalProperties;

    public function __construct(
        AttributeIdentifier $identifier,
        LabelCollection $labelCollection,
        string $type,
        bool $localizable,
        bool $scopable,
        bool $isRequired,
        array $additionalProperties
    ) {
        $this->identifier = $identifier;
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

    public function normalize(): array
    {
        $commonProperties = [
            'code' => $this->identifier->normalize(),
            'labels' => $this->labelCollection->normalize(),
            'type' => $this->mapAttributeType($this->type),
            'localizable' => $this->localizable,
            'scopable' => $this->scopable,
            'is_required_for_completeness' => $this->isRequired
        ];

        return array_merge($commonProperties, $this->additionalProperties);
    }
}

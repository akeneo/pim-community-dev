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

namespace Akeneo\ReferenceEntity\Domain\Query\Attribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeDetails
{
    public const IDENTIFIER = 'identifier';
    public const REFERENCE_ENTITY_IDENTIFIER = 'reference_entity_identifier';
    public const CODE = 'code';
    public const LABELS = 'labels';
    public const IS_REQUIRED = 'is_required';
    public const ORDER = 'order';
    public const VALUE_PER_LOCALE = 'value_per_locale';
    public const VALUE_PER_CHANNEL = 'value_per_channel';
    public const TYPE = 'type';

    /** @var string */
    public $type;

    /** @var string */
    public $identifier;

    /** @var string */
    public $referenceEntityIdentifier;

    /** @var string */
    public $code;

    /** @var array */
    public $labels;

    /** @var int */
    public $order;

    /** @var bool */
    public $isRequired;

    /** @var bool */
    public $valuePerChannel;

    /** @var bool */
    public $valuePerLocale;

    /** @var array */
    public $additionalProperties;

    public function normalize(): array
    {
        $commonProperties = [
            self::TYPE => $this->type,
            self::IDENTIFIER => $this->identifier,
            self::REFERENCE_ENTITY_IDENTIFIER => $this->referenceEntityIdentifier,
            self::CODE => $this->code,
            self::LABELS => $this->labels,
            self::IS_REQUIRED => $this->isRequired,
            self::ORDER => $this->order,
            self::VALUE_PER_LOCALE => $this->valuePerLocale,
            self::VALUE_PER_CHANNEL => $this->valuePerChannel,
        ];

        return array_merge($commonProperties, $this->additionalProperties);
    }
}

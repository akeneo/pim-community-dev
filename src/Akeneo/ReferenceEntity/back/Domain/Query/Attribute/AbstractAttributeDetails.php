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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAttributeDetails
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

    /** @var AttributeIdentifier */
    public $identifier;

    /** @var ReferenceEntity */
    public $referenceEntityIdentifier;

    /** @var AttributeCode */
    public $code;

    /** @var LabelCollection */
    public $labels;

    /** @var AttributeOrder */
    public $order;

    /** @var AttributeIsRequired */
    public $isRequired;

    /** @var AttributeValuePerChannel */
    public $valuePerChannel;

    /** @var AttributeValuePerLocale */
    public $valuePerLocale;

    public function normalize(): array
    {
        return [
            self::IDENTIFIER                 => $this->identifier->normalize(),
            self::REFERENCE_ENTITY_IDENTIFIER => (string) $this->referenceEntityIdentifier,
            self::CODE                       => (string) $this->code,
            self::LABELS                     => $this->labels->normalize(),
            self::IS_REQUIRED                => $this->isRequired->normalize(),
            self::ORDER                      => $this->order->intValue(),
            self::VALUE_PER_LOCALE           => $this->valuePerLocale->normalize(),
            self::VALUE_PER_CHANNEL          => $this->valuePerChannel->normalize(),
        ];
    }
}

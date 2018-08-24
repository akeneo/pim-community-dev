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

namespace Akeneo\EnrichedEntity\Domain\Query\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAttributeDetails
{
    public const IDENTIFIER = 'identifier';
    public const ENRICHED_ENTITY_IDENTIFIER = 'enriched_entity_identifier';
    public const CODE = 'code';
    public const LABELS = 'labels';
    public const IS_REQUIRED = 'is_required';
    public const ORDER = 'order';
    public const VALUE_PER_LOCALE = 'value_per_locale';
    public const VALUE_PER_CHANNEL = 'value_per_channel';
    public const TYPE = 'type';

    /** @var AttributeIdentifier */
    public $identifier;

    /** @var EnrichedEntity */
    public $enrichedEntityIdentifier;

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
            self::ENRICHED_ENTITY_IDENTIFIER => (string) $this->enrichedEntityIdentifier,
            self::CODE                       => (string) $this->code,
            self::LABELS                     => $this->labels->normalize(),
            self::IS_REQUIRED                => $this->isRequired->normalize(),
            self::ORDER                      => $this->order->intValue(),
            self::VALUE_PER_LOCALE           => $this->valuePerLocale->normalize(),
            self::VALUE_PER_CHANNEL          => $this->valuePerChannel->normalize(),
        ];
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich;

use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeDetails as DomainAttributeDetails;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\LabelCollection;

class AttributeDetails
{
    public const CODE = 'code';
    public const REFERENCE_ENTITY_CODE = 'reference_entity_code';
    public const LABELS = 'labels';
    public const IS_REQUIRED = 'is_required';
    public const ORDER = 'order';
    public const VALUE_PER_CHANNEL = 'value_per_channel';
    public const VALUE_PER_LOCALE = 'value_per_locale';
    public const TYPE = 'type';

    public function __construct(
        public readonly string $code,
        public readonly string $referenceEntityCode,
        public readonly LabelCollection $labels,
        public readonly bool $isRequired,
        public readonly int $order,
        public readonly bool $valuePerChannel,
        public readonly bool $valuePerLocale,
        public readonly string $type,
        public readonly array $additionalProperties = [],
    ) {
    }

    public function normalize(): array
    {
        $commonProperties = [
            self::CODE => $this->code,
            self::REFERENCE_ENTITY_CODE => $this->referenceEntityCode,
            self::LABELS => $this->labels->labels,
            self::IS_REQUIRED => $this->isRequired,
            self::ORDER => $this->order,
            self::VALUE_PER_CHANNEL => $this->valuePerChannel,
            self::VALUE_PER_LOCALE => $this->valuePerLocale,
            self::TYPE => $this->type,
        ];

        return [...$commonProperties, ...$this->additionalProperties];
    }

    public static function fromDomain(DomainAttributeDetails $attributeDetails): self
    {
        return new self(
            $attributeDetails->code,
            $attributeDetails->referenceEntityIdentifier,
            LabelCollection::fromArray($attributeDetails->labels),
            $attributeDetails->isRequired,
            $attributeDetails->order,
            $attributeDetails->valuePerChannel,
            $attributeDetails->valuePerLocale,
            $attributeDetails->type,
        );
    }
}

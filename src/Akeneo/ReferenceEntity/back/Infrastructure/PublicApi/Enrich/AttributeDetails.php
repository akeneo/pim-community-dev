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
    public const IDENTIFIER = 'identifier';
    public const CODE = 'code';
    public const LABELS = 'labels';
    public const VALUE_PER_CHANNEL = 'value_per_channel';
    public const VALUE_PER_LOCALE = 'value_per_locale';
    public const TYPE = 'type';

    private function __construct(
        public readonly string $identifier,
        public readonly string $code,
        public readonly LabelCollection $labels,
        public readonly bool $valuePerChannel,
        public readonly bool $valuePerLocale,
        public readonly string $type,
    ) {
    }

    public function normalize(): array
    {
        return [
            self::IDENTIFIER => $this->identifier,
            self::CODE => $this->code,
            self::LABELS => $this->labels->labels,
            self::VALUE_PER_CHANNEL => $this->valuePerChannel,
            self::VALUE_PER_LOCALE => $this->valuePerLocale,
            self::TYPE => $this->type,
        ];
    }

    public static function fromDomain(DomainAttributeDetails $attributeDetails): self
    {
        return new self(
            $attributeDetails->identifier,
            $attributeDetails->code,
            LabelCollection::fromArray($attributeDetails->labels),
            $attributeDetails->valuePerChannel,
            $attributeDetails->valuePerLocale,
            $attributeDetails->type,
        );
    }
}

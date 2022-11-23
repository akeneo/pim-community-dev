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
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityDetails as DomainReferenceEntityDetails;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\LabelCollection;

class ReferenceEntityDetails
{
    public const CODE = 'code';
    public const LABELS = 'labels';
    public const RECORD_COUNT = 'record_count';
    public const ATTRIBUTES = 'attributes';

    public function __construct(
        public readonly string $code,
        public readonly LabelCollection $labels,
        public readonly int $recordCount,
        /** @var AttributeDetails[] */
        public readonly array $attributes,
    ) {
    }

    public function normalize(): array
    {
        return [
            self::CODE => $this->code,
            self::LABELS => $this->labels->labels,
            self::RECORD_COUNT => $this->recordCount,
            self::ATTRIBUTES => array_map(
                static fn (AttributeDetails $attribute) => $attribute->normalize(),
                $this->attributes,
            ),
        ];
    }

    public static function fromDomain(DomainReferenceEntityDetails $referenceEntityDetails): self
    {
        $attributes = array_map(
            static fn (DomainAttributeDetails $domainAttribute) => AttributeDetails::fromDomain($domainAttribute),
            $referenceEntityDetails->attributes,
        );

        return new self(
            (string) $referenceEntityDetails->identifier,
            LabelCollection::fromArray($referenceEntityDetails->labels->normalize()),
            $referenceEntityDetails->recordCount,
            $attributes,
        );
    }
}

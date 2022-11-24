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

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeDetails as DomainAttributeDetails;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityDetails;

class FindReferenceEntityAttributes implements FindReferenceEntityAttributesInterface
{
    public function __construct(
        private FindReferenceEntityDetailsInterface $findReferenceEntityDetails,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function findByCode(string $referenceEntityCode, ?array $types = null): array
    {
        $referenceEntityDetails = $this->findReferenceEntityDetails->find(
            ReferenceEntityIdentifier::fromString($referenceEntityCode),
        );

        if (!$referenceEntityDetails instanceof ReferenceEntityDetails) {
            return [];
        }

        $filteredAttributes = $this->filterOnTypes($referenceEntityDetails->attributes, $types);

        return array_map(
            static fn (DomainAttributeDetails $attributeDetails) => AttributeDetails::fromDomain($attributeDetails),
            $filteredAttributes,
        );
    }

    private function filterOnTypes(array $attributes, ?array $types): array
    {
        if (null === $types) {
            return $attributes;
        }

        return array_filter(
            $attributes,
            static fn (DomainAttributeDetails $attribute) => in_array($attribute->type, $types),
        );
    }
}

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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeDetails;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesDetailsInterface;

class FindOptionAttributeLabels implements FindOptionAttributeLabelsInterface
{
    public function __construct(
        private FindAttributesDetailsInterface $findAttributesDetails,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $attributeIdentifier): array
    {
        $attributeDetails = $this->findAttributesDetails->findByIdentifier(
            AttributeIdentifier::fromString($attributeIdentifier),
        );

        if (
            !$attributeDetails instanceof AttributeDetails
            || OptionAttribute::ATTRIBUTE_TYPE !== $attributeDetails->type
        ) {
            return [];
        }

        $labels = [];
        foreach ($attributeDetails->additionalProperties['options'] as $option) {
            $labels[$option['code']] = $option['labels'];
        }

        return $labels;
    }
}

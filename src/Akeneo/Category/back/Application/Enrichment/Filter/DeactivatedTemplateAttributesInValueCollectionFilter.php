<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Enrichment\Filter;

use Akeneo\Category\Domain\Query\DeactivatedTemplateAttributes\DeactivatedTemplateAttributeIdentifier;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeactivatedTemplateAttributesInValueCollectionFilter
{
    /**
     * @param array<DeactivatedTemplateAttributeIdentifier> $deactivatedAttributes
     * @param array<string, string|null> $rawCategory
     *
     * @return array<string, string|null>
     *
     * @throws \JsonException
     */
    public function __invoke(array $deactivatedAttributes, array $rawCategory): array
    {
        if (empty($deactivatedAttributes) || empty($rawCategory['value_collection'])) {
            return $rawCategory;
        }

        $valueCollection = json_decode($rawCategory['value_collection'], true, 512, JSON_THROW_ON_ERROR);

        foreach ($deactivatedAttributes as $deactivatedAttribute) {
            $attributeCode = $deactivatedAttribute->code.AbstractValue::SEPARATOR.$deactivatedAttribute->uuid;
            $valueCollection = array_filter($valueCollection, static function ($rawValue) use ($attributeCode) {
                return $rawValue['attribute_code'] !== $attributeCode;
            });
        }

        $rawCategory['value_collection'] = json_encode($valueCollection, JSON_THROW_ON_ERROR);

        return $rawCategory;
    }
}

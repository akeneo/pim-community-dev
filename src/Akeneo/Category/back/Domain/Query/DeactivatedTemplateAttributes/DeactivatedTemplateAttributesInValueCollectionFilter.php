<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Query\DeactivatedTemplateAttributes;

use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeactivatedTemplateAttributesInValueCollectionFilter
{
    /**
     * @param array<DeactivatedTemplateAttributeIdentifier> $deactivatedAttributes
     * @param array<string, array<string, mixed>> $decodedRawValueCollection
     *
     * @return array<string, array<string, mixed>>
     */
    public function __invoke(array $deactivatedAttributes, array $decodedRawValueCollection): array
    {
        foreach ($deactivatedAttributes as $deactivatedAttribute) {
            $attributeCode = $deactivatedAttribute->code.AbstractValue::SEPARATOR.$deactivatedAttribute->uuid;
            $decodedRawValueCollection = array_filter($decodedRawValueCollection, static function ($rawValue) use ($attributeCode) {
                return $rawValue['attribute_code'] !== $attributeCode;
            });
        }

        return $decodedRawValueCollection;
    }
}

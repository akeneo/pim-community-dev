<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Enrichment;

use Akeneo\Category\Domain\Query\DeactivatedTemplateAttributeIdentifier;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeactivatedTemplateAttributesInValueCollectionCleaner
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
        if (empty($deactivatedAttributes) || empty($rawCategory['value_collection']) ) {
            return $rawCategory;
        }

        foreach ($deactivatedAttributes as $deactivatedAttribute) {
            $decodedRawValueCollection = json_decode(
                $rawCategory['value_collection'],
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
            $attributeCode = $deactivatedAttribute->code.AbstractValue::SEPARATOR.$deactivatedAttribute->uuid;
            foreach ($decodedRawValueCollection as $key => $rawValue) {
                if ($rawValue['attribute_code'] === $attributeCode) {
                    unset($decodedRawValueCollection[$key]);
                }
            }
        }
        $rawCategory['value_collection'] = json_encode($decodedRawValueCollection, JSON_THROW_ON_ERROR);

        return $rawCategory;
    }
}

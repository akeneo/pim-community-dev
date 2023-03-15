<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Enrichment\Filter;

use Akeneo\Category\Domain\ValueObject\Attribute\Value\Value;
use Akeneo\Category\Domain\ValueObject\ValueCollection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ByTemplateAttributesUuidsFilter
{
    /**
     * @param array<string, string> $templateAttributes
     *
     * @return array<Value>
     */
    public static function getEnrichedValuesToClean(ValueCollection $enrichedValues, array $templateAttributes): array
    {
        $valuesToRemove = [];
        if (empty($templateAttributes)) {
            return [];
        }

        foreach ($enrichedValues as $enrichedValue) {
            $valueAttributeUuid = (string) $enrichedValue->getUuid();
            $valueAttributeCode = (string) $enrichedValue->getCode();
            if (
                array_key_exists($valueAttributeUuid, $templateAttributes)
                && in_array($valueAttributeCode, $templateAttributes, true)
            ) {
                $valuesToRemove[] = $enrichedValue;
            }
        }

        return $valuesToRemove;
    }
}

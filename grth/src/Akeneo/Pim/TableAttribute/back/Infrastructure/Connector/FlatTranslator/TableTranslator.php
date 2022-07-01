<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue\FlatAttributeValueTranslatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\Value\Cell;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\Values\TableValueTranslatorRegistry;

final class TableTranslator implements FlatAttributeValueTranslatorInterface
{
    public function __construct(
        private TableValueTranslatorRegistry $tableValueTranslatorRegistry,
        private TableColumnTranslator $tableColumnTranslator
    ) {
    }

    public function supports(string $attributeType, string $columnName): bool
    {
        return AttributeTypes::TABLE === $attributeType;
    }

    /**
     * Translate multiple json values
     *
     * @param string[] $values for example:
     *  [
     *      '[{"ingredient":"salt","is_allergenic":true}, ...]'
     *      '[{"ingredient":"sugar","is_allergenic":false}, ...]'
     *  ]
     * @return string[] for example:
     *  [
     *      '[{"Ingredient":"Salt","[is_allergenic]":"Yes"}, ...]'
     *      '[{"Ingredient":"[sugar]","[is_allergenic]":"No"}, ...]'
     *  ]
     *
     */
    public function translate(string $attributeCode, array $properties, array $values, string $locale): array
    {
        $indexedColumnLabels = $this->tableColumnTranslator->getTableColumnLabels($attributeCode, $locale);
        foreach ($values as $key => $value) {
            if ('' === $value) {
                continue;
            }

            $decodedValue = \json_decode($value, true);
            foreach ($decodedValue as $index => $row) {
                foreach ($row as $columnCode => $cellValue) {
                    $label = $indexedColumnLabels[$columnCode]
                        ?? \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $columnCode);
                    unset($decodedValue[$index][$columnCode]);
                    $decodedValue[$index][$label] = $this->tableValueTranslatorRegistry->translate(
                        $attributeCode,
                        (string) $columnCode,
                        $locale,
                        Cell::fromNormalized($cellValue)->asString()
                    );
                }
            }

            $values[$key] = \json_encode($decodedValue, JSON_UNESCAPED_UNICODE);
        }

        return $values;
    }
}

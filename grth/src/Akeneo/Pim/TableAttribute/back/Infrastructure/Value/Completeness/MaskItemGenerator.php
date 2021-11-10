<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Value\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\MaskItemGeneratorForAttributeType;
use Akeneo\Pim\Structure\Component\AttributeTypes;

final class MaskItemGenerator implements MaskItemGeneratorForAttributeType
{
    public function forRawValue(string $attributeCode, string $channelCode, string $localeCode, $value): array
    {
        if (count($value) === 0) {
            return [];
        }

        $counts = [];
        $max = 0;
        foreach ($value as $row) {
            foreach (array_keys($row) as $columnCode) {
                if (!isset($counts[$columnCode])) {
                    $counts[$columnCode] = 1;
                } else {
                    $counts[$columnCode]++;
                }
            }
            $max = \max($max, $counts[$columnCode]);
        }

        $filledColumns = [];
        foreach ($counts as $colunmCode => $count) {
            if ($count === $max) {
                $filledColumns[] = $colunmCode;
            }
        }

        sort($filledColumns);

        $result = [];
        foreach ($this->getFilledColumnsCombinations($filledColumns) as $combination) {
            $result[] = sprintf(
                '%s-%s-%s-%s',
                $attributeCode,
                join('-', $combination),
                $channelCode,
                $localeCode
            );
        };

        return $result;
    }

    public function supportedAttributeTypes(): array
    {
        return [AttributeTypes::TABLE];
    }

    private function getFilledColumnsCombinations($filledColumns)
    {
        $combinations = [[]];

        foreach ($filledColumns as $filledColumn) {
            foreach ($combinations as $combination) {
                array_push($combinations, array_merge($combination, [$filledColumn]));
            }
        }
        unset($combinations[0]);

        return $combinations;
    }
}

<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;

use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EmptySelectValuesCleaner implements EmptyValuesCleaner
{
    public function clean(OnGoingCleanedRawValues $onGoingCleanedRawValues): OnGoingCleanedRawValues
    {
        $selectValues = $onGoingCleanedRawValues->nonCleanedValuesOfTypes(AttributeTypes::OPTION_SIMPLE_SELECT, AttributeTypes::OPTION_MULTI_SELECT);

        if (empty($selectValues)) {
            return $onGoingCleanedRawValues;
        }

        $cleanedValues = [];
        $cleanedValues[AttributeTypes::OPTION_SIMPLE_SELECT] = [];
        $cleanedValues[AttributeTypes::OPTION_MULTI_SELECT] = [];

        foreach ($selectValues as $attributeCode => $valueCollection) {
            foreach ($valueCollection as $values) {
                $multiSelectValues = [];
                $simpleSelectValues = [];
                foreach ($values['values'] as $channel => $channelValues) {
                    foreach ($channelValues as $locale => $value) {
                        if (is_array($value)) {
                            if (!empty($value)) {
                                $multiSelectValues[$channel][$locale] = $value;
                            }
                        } else {
                            if (trim($value) !== '') {
                                $simpleSelectValues[$channel][$locale] = $value;
                            }
                        }
                    }
                }

                if ($multiSelectValues !== []) {
                    $cleanedValues[AttributeTypes::OPTION_MULTI_SELECT][$attributeCode][] = [
                        'identifier' => $values['identifier'],
                        'values' => $multiSelectValues,
                    ];
                }
                if ($simpleSelectValues !== []) {
                    $cleanedValues[AttributeTypes::OPTION_SIMPLE_SELECT][$attributeCode][] = [
                        'identifier' => $values['identifier'],
                        'values' => $simpleSelectValues,
                    ];
                }
            }
        }



        return $onGoingCleanedRawValues->addCleanedValuesIndexedByType($cleanedValues);
    }
}

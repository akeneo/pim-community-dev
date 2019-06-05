<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NonExistentMultiSelectValuesFilter implements NonExistentValuesFilter
{
    /** @var GetExistingAttributeOptionCodes */
    private $getExistingAttributeOptionCodes;

    public function __construct(GetExistingAttributeOptionCodes $getExistingAttributeOptionCodes)
    {
        $this->getExistingAttributeOptionCodes = $getExistingAttributeOptionCodes;
    }

    public function filter(OnGoingFilteredRawValues $onGoingFilteredRawValues): OnGoingFilteredRawValues
    {
        $selectValues = $onGoingFilteredRawValues->notFilteredValuesOfTypes(AttributeTypes::OPTION_MULTI_SELECT);

        if (empty($selectValues)) {
            return $onGoingFilteredRawValues;
        }

        $optionCodes = $this->getExistingAttributeOptionCodes->fromOptionCodesByAttributeCode(
            $this->getOptionCodes($selectValues)
        );

        $filteredValues = [];

        foreach ($selectValues as $attributeCode => $productValueCollection) {
            foreach ($productValueCollection as $productValues) {
                $multiSelectValues = [];

                foreach ($productValues['values'] as $channel => $channelValues) {
                    foreach ($channelValues as $locale => $value) {
                        if (is_array($value)) {
                            $multiSelectValues[$channel][$locale] = array_intersect(
                                $value,
                                $optionCodes[$attributeCode] ?? []
                            );
                        }
                    }
                }

                if ($multiSelectValues !== []) {
                    $filteredValues[AttributeTypes::OPTION_MULTI_SELECT][$attributeCode][] = [
                        'identifier' => $productValues['identifier'],
                        'values' => $multiSelectValues,
                    ];
                }
            }
        }

        return $onGoingFilteredRawValues->addFilteredValuesIndexedByType($filteredValues);
    }

    private function getExistingOptionCodes(array $selectValues): array
    {
        $optionCodes = $this->getOptionCodes($selectValues);

        return $this->getExistingAttributeOptionCodes->fromOptionCodesByAttributeCode($optionCodes);
    }

    private function getOptionCodes(array $selectValues): array
    {
        $optionCodes = [];

        foreach ($selectValues as $attributeCode => $valueCollection) {
            foreach ($valueCollection as $values) {
                foreach ($values['values'] as $channel => $channelValues) {
                    foreach ($channelValues as $locale => $value) {
                        if (is_array($value)) {
                            foreach ($value as $optionCode) {
                                $optionCodes[$attributeCode][] = $optionCode;
                            }
                        }
                    }
                }
            }
        }

        $uniqueOptionCodes = [];
        foreach ($optionCodes as $attributeCode => $optionCodeForThisAttribute) {
            $uniqueOptionCodes[$attributeCode] = array_unique($optionCodeForThisAttribute);
        }

        return $uniqueOptionCodes;
    }
}

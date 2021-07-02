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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\MultiSelect;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionHandlerInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\MultiSelectValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;

class MultiSelectLabelSelectionHandler implements SelectionHandlerInterface
{
    private GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues;

    public function __construct(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $this->getExistingAttributeOptionsWithValues = $getExistingAttributeOptionsWithValues;
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (!$value instanceof MultiSelectValue || !$selection instanceof MultiSelectLabelSelection) {
            throw new \InvalidArgumentException('Cannot apply Multi Select selection on this entity');
        }

        $attributeCode = $selection->getAttributeCode();
        $optionsCodes = $value->getData();
        $optionsKeys = $this->generateOptionsKeys($optionsCodes, $attributeCode);

        $attributeOptionTranslations = $this->getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(
            $optionsKeys
        );

        $selectedData = array_map(function ($optionCode) use ($attributeOptionTranslations, $attributeCode, $selection) {
            $optionKey = $this->generateOptionKey($attributeCode, $optionCode);
            return $attributeOptionTranslations[$optionKey][$selection->getLocale()] ?? sprintf('[%s]', $optionCode);
        }, $value->getData());

        return implode($selection->getSeparator(), $selectedData);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof MultiSelectLabelSelection
            && $value instanceof MultiSelectValue;
    }

    private function generateOptionsKeys(array $optionsCodes, string $attributeCode): array
    {
        return array_map(
            function ($optionCode) use ($attributeCode) {
                return $this->generateOptionKey($attributeCode, $optionCode);
            },
            $optionsCodes
        );
    }

    private function generateOptionKey(string $attributeCode, string $optionCode): string
    {
        return sprintf('%s.%s', $attributeCode, $optionCode);
    }
}

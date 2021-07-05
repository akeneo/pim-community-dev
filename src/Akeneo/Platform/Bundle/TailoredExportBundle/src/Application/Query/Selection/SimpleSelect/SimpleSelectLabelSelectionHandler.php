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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\SimpleSelect;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionHandlerInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\SimpleSelectValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;

class SimpleSelectLabelSelectionHandler implements SelectionHandlerInterface
{
    private GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues;

    public function __construct(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues
    ) {
        $this->getExistingAttributeOptionsWithValues = $getExistingAttributeOptionsWithValues;
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (!$value instanceof SimpleSelectValue || !$selection instanceof SimpleSelectLabelSelection) {
            throw new \InvalidArgumentException('Cannot apply Simple Select selection on this entity');
        }

        $attributeCode = $selection->getAttributeCode();
        $optionCode = $value->getOptionCode();
        $optionKey = sprintf('%s.%s', $attributeCode, $optionCode);
        $attributeOptionTranslations = $this->getExistingAttributeOptionsWithValues
            ->fromAttributeCodeAndOptionCodes([$optionKey]);

        return $attributeOptionTranslations[$optionKey][$selection->getLocale()] ?? sprintf('[%s]', $optionCode);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof SimpleSelectLabelSelection
            && $value instanceof SimpleSelectValue;
    }
}

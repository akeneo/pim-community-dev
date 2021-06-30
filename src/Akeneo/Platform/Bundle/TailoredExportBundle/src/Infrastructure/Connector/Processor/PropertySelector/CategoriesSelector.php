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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\PropertySelector;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Category\GetCategoryTranslations;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\CategoriesValue;
use Akeneo\Platform\TailoredExport\Domain\SelectionTypes;

class CategoriesSelector implements PropertySelectorInterface
{
    private GetCategoryTranslations $getCategoryTranslations;

    public function __construct(
        GetCategoryTranslations $getCategoryTranslations
    ) {
        $this->getCategoryTranslations = $getCategoryTranslations;
    }

    public function applySelection(array $selectionConfiguration, SourceValueInterface $sourceValue): string
    {
        if (!$sourceValue instanceof CategoriesValue) {
            throw new \LogicException('Cannot apply Categories selection on this entity');
        }

        $categoryCodes = $sourceValue->getData();

        switch ($selectionConfiguration['type']) {
            case SelectionTypes::CODE:
                $selectedData = $categoryCodes;
                break;
            case SelectionTypes::LABEL:
                $categoryTranslations = $this->getCategoryTranslations
                    ->byCategoryCodesAndLocale($categoryCodes, $selectionConfiguration['locale']);

                $selectedData = array_map(fn ($categoryCode) => $categoryTranslations[$categoryCode] ??
                    sprintf('[%s]', $categoryCode), $categoryCodes);

                break;
            default:
                throw new \LogicException(sprintf('Selection type "%s" is not supported', $selectionConfiguration['type']));
        }

        return implode($selectionConfiguration['separator'], $selectedData);
    }

    public function supports(array $selectionConfiguration, SourceValueInterface $sourceValue): bool
    {
        return in_array($selectionConfiguration['type'], [SelectionTypes::LABEL, SelectionTypes::CODE])
            && $sourceValue instanceof CategoriesValue;
    }
}

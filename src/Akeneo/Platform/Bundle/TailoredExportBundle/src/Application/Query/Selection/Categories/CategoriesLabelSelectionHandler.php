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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\Categories;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Category\GetCategoryTranslations;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionHandlerInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\CategoriesValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;

class CategoriesLabelSelectionHandler implements SelectionHandlerInterface
{
    private GetCategoryTranslations $getCategoryTranslations;

    public function __construct(GetCategoryTranslations $getCategoryTranslations)
    {
        $this->getCategoryTranslations = $getCategoryTranslations;
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof CategoriesLabelSelection
            ||!$value instanceof CategoriesValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Categories selection on this entity');
        }

        $categoryCodes = $value->getCategoryCodes();

        $categoryTranslations = $this->getCategoryTranslations
            ->byCategoryCodesAndLocale($categoryCodes, $selection->getLocale());

        $selectedData = array_map(fn ($categoryCode) => $categoryTranslations[$categoryCode] ??
            sprintf('[%s]', $categoryCode), $categoryCodes);

        return implode($selection->getSeparator(), $selectedData);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $value instanceof CategoriesValue
            && $selection instanceof CategoriesLabelSelection;
    }
}

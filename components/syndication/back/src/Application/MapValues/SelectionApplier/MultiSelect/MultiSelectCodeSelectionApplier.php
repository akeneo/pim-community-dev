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

namespace Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\MultiSelect;

use Akeneo\Platform\Syndication\Application\Common\Selection\MultiSelect\MultiSelectCodeSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\MultiSelectValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\StringCollectionTarget;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;

class MultiSelectCodeSelectionApplier implements SelectionApplierInterface
{
    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value)
    {
        if (
            !$selection instanceof MultiSelectCodeSelection
            || !$value instanceof MultiSelectValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Multi Select selection on this entity');
        }

        $optionsCodes = $value->getOptionCodes();

        $selectedData = array_map(static function ($optionCode) use ($value) {
            if ($value->hasMappedValue($optionCode)) {
                return $value->getMappedValue($optionCode);
            }

            return $optionCode;
        }, $optionsCodes);

        if ($target instanceof StringCollectionTarget) {
            return $selectedData;
        }

        return implode($selection->getSeparator(), $selectedData);
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof MultiSelectCodeSelection
            && $value instanceof MultiSelectValue;
    }
}

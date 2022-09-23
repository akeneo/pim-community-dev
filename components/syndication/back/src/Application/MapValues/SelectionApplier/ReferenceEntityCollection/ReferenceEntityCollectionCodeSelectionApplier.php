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

namespace Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\ReferenceEntityCollection;

use Akeneo\Platform\Syndication\Application\Common\Selection\ReferenceEntityCollection\ReferenceEntityCollectionCodeSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\ReferenceEntityCollectionValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;

class ReferenceEntityCollectionCodeSelectionApplier implements SelectionApplierInterface
{
    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof ReferenceEntityCollectionCodeSelection
            || !$value instanceof ReferenceEntityCollectionValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Reference Entity Collection selection on this entity');
        }

        $optionsCodes = $value->getRecordCodes();
        $selectedData = array_map(
            static fn ($recordCode) =>
            $value->hasMappedValue($recordCode) ? $value->getMappedValue($recordCode) : $recordCode,
            $optionsCodes
        );

        return implode($selection->getSeparator(), $selectedData);
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof ReferenceEntityCollectionCodeSelection
            && $value instanceof ReferenceEntityCollectionValue;
    }
}

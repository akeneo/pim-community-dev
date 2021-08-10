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

namespace Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\ReferenceEntityCollection;

use Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\ReferenceEntityCollection\ReferenceEntityCollectionCodeSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\ReferenceEntityCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;

class ReferenceEntityCollectionCodeSelectionApplier implements SelectionApplierInterface
{
    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof ReferenceEntityCollectionCodeSelection
            || !$value instanceof ReferenceEntityCollectionValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Reference Entity Collection selection on this entity');
        }

        return implode($selection->getSeparator(), $value->getRecordCodes());
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof ReferenceEntityCollectionCodeSelection
            && $value instanceof ReferenceEntityCollectionValue;
    }
}

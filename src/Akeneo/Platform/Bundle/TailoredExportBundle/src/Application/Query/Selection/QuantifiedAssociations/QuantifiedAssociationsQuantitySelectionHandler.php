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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\QuantifiedAssociations;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionHandlerInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\QuantifiedAssociationsValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;

class QuantifiedAssociationsQuantitySelectionHandler implements SelectionHandlerInterface
{
    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof QuantifiedAssociationsQuantitySelection
            || !$value instanceof QuantifiedAssociationsValue
        ) {
            throw new \InvalidArgumentException('Cannot apply quantified associations selection on this entity');
        }

        $associatedEntityCodes = $this->getAssociatedEntityQuantities($selection, $value);

        return \implode($selection->getSeparator(), $associatedEntityCodes);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof QuantifiedAssociationsQuantitySelection
            && $value instanceof QuantifiedAssociationsValue;
    }

    /**
     * @return string[]
     */
    private function getAssociatedEntityQuantities(QuantifiedAssociationsQuantitySelection $selection, QuantifiedAssociationsValue $value): array
    {
        if ($selection->isProductsSelection()) {
            return $value->getAssociatedProductQuantities();
        } elseif ($selection->isProductModelsSelection()) {
            return $value->getAssociatedProductModelQuantities();
        }

        throw new \InvalidArgumentException('Entity type is not supported in this selection');
    }
}

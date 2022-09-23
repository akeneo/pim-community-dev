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

namespace Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\QuantifiedAssociations;

use Akeneo\Platform\Syndication\Application\Common\Selection\QuantifiedAssociations\QuantifiedAssociationsQuantitySelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\QuantifiedAssociationsValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;

class QuantifiedAssociationsQuantitySelectionApplier implements SelectionApplierInterface
{
    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof QuantifiedAssociationsQuantitySelection
            || !$value instanceof QuantifiedAssociationsValue
        ) {
            throw new \InvalidArgumentException('Cannot apply quantified associations quantity selection on this entity');
        }

        $associatedEntityCodes = $this->getAssociatedEntityQuantities($selection, $value);

        return \implode($selection->getSeparator(), $associatedEntityCodes);
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof QuantifiedAssociationsQuantitySelection
            && $value instanceof QuantifiedAssociationsValue;
    }

    /**
     * @return int[]
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

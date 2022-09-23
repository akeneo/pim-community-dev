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

namespace Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SimpleAssociations;

use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\Selection\SimpleAssociations\SimpleAssociationsCodeSelection;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SimpleAssociationsValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;

class SimpleAssociationsCodeSelectionApplier implements SelectionApplierInterface
{
    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof SimpleAssociationsCodeSelection
            || !$value instanceof SimpleAssociationsValue
        ) {
            throw new \InvalidArgumentException('Cannot apply simple associations code selection on this entity');
        }

        $associatedEntityCodes = $this->getAssociatedEntityCodes($selection, $value);

        return \implode($selection->getSeparator(), $associatedEntityCodes);
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof SimpleAssociationsCodeSelection
            && $value instanceof SimpleAssociationsValue;
    }

    /**
     * @return string[]
     */
    private function getAssociatedEntityCodes(SimpleAssociationsCodeSelection $selection, SimpleAssociationsValue $value): array
    {
        if ($selection->isProductsSelection()) {
            return $value->getAssociatedProductIdentifiers();
        } elseif ($selection->isProductModelsSelection()) {
            return $value->getAssociatedProductModelCodes();
        } elseif ($selection->isGroupsSelection()) {
            return $value->getAssociatedGroupCodes();
        }

        throw new \InvalidArgumentException('Entity type is not supported in this selection');
    }
}

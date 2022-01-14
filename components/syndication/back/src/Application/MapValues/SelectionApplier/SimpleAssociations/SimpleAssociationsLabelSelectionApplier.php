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
use Akeneo\Platform\Syndication\Application\Common\Selection\SimpleAssociations\SimpleAssociationsLabelSelection;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SimpleAssociationsValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\Syndication\Domain\Query\FindProductLabelsInterface;
use Akeneo\Platform\Syndication\Domain\Query\FindProductModelLabelsInterface;

class SimpleAssociationsLabelSelectionApplier implements SelectionApplierInterface
{
    private FindProductLabelsInterface $findProductLabels;
    private FindProductModelLabelsInterface $findProductModelLabels;

    public function __construct(
        FindProductLabelsInterface $findProductLabels,
        FindProductModelLabelsInterface $findProductModelLabels
    ) {
        $this->findProductLabels = $findProductLabels;
        $this->findProductModelLabels = $findProductModelLabels;
    }

    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof SimpleAssociationsLabelSelection
            || !$value instanceof SimpleAssociationsValue
        ) {
            throw new \InvalidArgumentException('Cannot apply simple associations label selection on this entity');
        }

        $associatedEntityCodes = $this->getAssociatedEntityCodes($selection, $value);
        $associatedEntityLabels = $this->getAssociatedEntityLabels($selection, $associatedEntityCodes);

        $selectedData = \array_map(static fn ($associatedEntityCode) => $associatedEntityLabels[$associatedEntityCode] ??
            \sprintf('[%s]', $associatedEntityCode), $associatedEntityCodes);

        return \implode($selection->getSeparator(), $selectedData);
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof SimpleAssociationsLabelSelection
            && $value instanceof SimpleAssociationsValue;
    }

    private function getAssociatedEntityLabels(
        SimpleAssociationsLabelSelection $selection,
        array $associatedEntityCodes
    ): array {
        if ($selection->isProductsSelection()) {
            return $this->findProductLabels->byIdentifiers(
                $associatedEntityCodes,
                $selection->getChannel(),
                $selection->getLocale(),
            );
        } elseif ($selection->isProductModelsSelection()) {
            return $this->findProductModelLabels->byCodes(
                $associatedEntityCodes,
                $selection->getChannel(),
                $selection->getLocale(),
            );
        }

        throw new \InvalidArgumentException('Entity type is not supported in this selection');
    }

    /**
     * @return string[]
     */
    private function getAssociatedEntityCodes(SimpleAssociationsLabelSelection $selection, SimpleAssociationsValue $value): array
    {
        if ($selection->isProductsSelection()) {
            return $value->getAssociatedProductIdentifiers();
        } elseif ($selection->isProductModelsSelection()) {
            return $value->getAssociatedProductModelCodes();
        }

        throw new \InvalidArgumentException('Entity type is not supported in this selection');
    }
}

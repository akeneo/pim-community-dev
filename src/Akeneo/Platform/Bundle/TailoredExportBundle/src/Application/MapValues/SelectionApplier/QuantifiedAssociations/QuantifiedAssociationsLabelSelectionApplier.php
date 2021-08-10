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

namespace Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\QuantifiedAssociations;

use Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\QuantifiedAssociations\QuantifiedAssociationsLabelSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\QuantifiedAssociationsValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindProductLabelsInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindProductModelLabelsInterface;

class QuantifiedAssociationsLabelSelectionApplier implements SelectionApplierInterface
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

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof QuantifiedAssociationsLabelSelection
            || !$value instanceof QuantifiedAssociationsValue
        ) {
            throw new \InvalidArgumentException('Cannot apply quantified associations label selection on this entity');
        }

        $associatedEntityCodes = $this->getAssociatedEntityCodes($selection, $value);
        $associatedEntityLabels = $this->getAssociatedEntityLabels($selection, $associatedEntityCodes);

        $selectedData = $this->formatLabels($associatedEntityLabels, $associatedEntityCodes);

        return \implode($selection->getSeparator(), $selectedData);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof QuantifiedAssociationsLabelSelection
            && $value instanceof QuantifiedAssociationsValue;
    }

    private function getAssociatedEntityLabels(
        QuantifiedAssociationsLabelSelection $selection,
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
    private function getAssociatedEntityCodes(QuantifiedAssociationsLabelSelection $selection, QuantifiedAssociationsValue $value): array
    {
        if ($selection->isProductsSelection()) {
            return $value->getAssociatedProductIdentifiers();
        } elseif ($selection->isProductModelsSelection()) {
            return $value->getAssociatedProductModelCodes();
        }

        throw new \InvalidArgumentException('Entity type is not supported in this selection');
    }

    /**
     * @param string[] $associatedEntityLabels
     * @param array<string, string> $associatedEntityCodes
     * @return string[]
     */
    private function formatLabels(array $associatedEntityLabels, array $associatedEntityCodes): array
    {
        return \array_map(
            static fn ($associatedEntityCode) =>
                $associatedEntityLabels[$associatedEntityCode] ?? \sprintf('[%s]', $associatedEntityCode),
            $associatedEntityCodes
        );
    }
}

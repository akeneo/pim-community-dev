<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\ReferenceEntityCollection;

use Akeneo\Platform\TailoredExport\Application\Common\Selection\ReferenceEntityCollection\ReferenceEntityCollectionNumberAttributeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\ReferenceEntityCollectionValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindRecordsAttributeValueInterface;

class ReferenceEntityCollectionNumberAttributeSelectionApplier implements SelectionApplierInterface
{
    private const DEFAULT_DECIMAL_SEPARATOR = '.';

    public function __construct(
        private FindRecordsAttributeValueInterface $findRecordsAttributeValue,
    ) {
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (!$value instanceof ReferenceEntityCollectionValue || !$selection instanceof ReferenceEntityCollectionNumberAttributeSelection) {
            throw new \InvalidArgumentException('Cannot apply Reference Entity Collection selection on this entity');
        }

        $recordCodes = $value->getRecordCodes();
        $recordValues = array_change_key_case($this->findRecordsAttributeValue->find(
            $selection->getReferenceEntityCode(),
            $recordCodes,
            $selection->getReferenceEntityAttributeIdentifier(),
            $selection->getChannel(),
            $selection->getLocale(),
        ));

        $selectedData = array_map(static function (string $recordCode) use ($value, $recordValues, $selection) {
            if ($value->hasMappedValue($recordCode)) {
                return $value->getMappedValue($recordCode);
            }

            $recordValue = $recordValues[$recordCode] ?? null;

            if (null === $recordValue) {
                return '';
            }

            return str_replace(self::DEFAULT_DECIMAL_SEPARATOR, $selection->getDecimalSeparator(), (string) $recordValue);
        }, $recordCodes);

        return implode($selection->getSeparator(), $selectedData);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof ReferenceEntityCollectionNumberAttributeSelection
            && $value instanceof ReferenceEntityCollectionValue;
    }
}

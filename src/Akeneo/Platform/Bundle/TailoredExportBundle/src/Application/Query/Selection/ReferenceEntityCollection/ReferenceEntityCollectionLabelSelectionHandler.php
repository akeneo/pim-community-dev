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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\ReferenceEntityCollection;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionHandlerInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\ReferenceEntityCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\FindRecordsLabelTranslations;

class ReferenceEntityCollectionLabelSelectionHandler implements SelectionHandlerInterface
{
    private FindRecordsLabelTranslations $findRecordLabelTranslations;

    public function __construct(
        FindRecordsLabelTranslations $findRecordLabelTranslations
    ) {
        $this->findRecordLabelTranslations = $findRecordLabelTranslations;
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$value instanceof ReferenceEntityCollectionValue
            || !$selection instanceof ReferenceEntityCollectionLabelSelection
        ) {
            throw new \InvalidArgumentException('Cannot apply Reference Entity Collection selection on this entity');
        }

        $recordCodes = $value->getRecordCodes();
        $referenceEntityCode = $selection->getReferenceEntityCode();
        $recordTranslations = $this->findRecordLabelTranslations->find(
            $referenceEntityCode,
            $recordCodes,
            $selection->getLocale()
        );

        $selectedData = array_map(fn ($recordCode) => $recordTranslations[$recordCode] ??
            sprintf('[%s]', $recordCode), $recordCodes);

        return implode($selection->getSeparator(), $selectedData);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof ReferenceEntityCollectionLabelSelection
            && $value instanceof ReferenceEntityCollectionValue;
    }
}

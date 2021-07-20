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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\ReferenceEntity;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionHandlerInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\ReferenceEntityValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\FindRecordsLabelTranslationsInterface;

class ReferenceEntityLabelSelectionHandler implements SelectionHandlerInterface
{
    private FindRecordsLabelTranslationsInterface $findRecordLabelTranslations;

    public function __construct(FindRecordsLabelTranslationsInterface $findRecordLabelTranslations)
    {
        $this->findRecordLabelTranslations = $findRecordLabelTranslations;
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (!$value instanceof ReferenceEntityValue || !$selection instanceof ReferenceEntityLabelSelection) {
            throw new \InvalidArgumentException('Cannot apply Reference Entity selection on this entity');
        }

        $recordCode = $value->getRecordCode();
        $referenceEntityCode = $selection->getReferenceEntityCode();
        $recordTranslations = $this->findRecordLabelTranslations->find(
            $referenceEntityCode,
            [$recordCode],
            $selection->getLocale()
        );

        return $recordTranslations[$recordCode] ?? sprintf('[%s]', $recordCode);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof ReferenceEntityLabelSelection
            && $value instanceof ReferenceEntityValue;
    }
}

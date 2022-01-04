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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query\ReferenceEntity;

use Akeneo\Platform\TailoredExport\Domain\Query\FindRecordLabelsInterface;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\FindRecordsLabelTranslationsInterface;

class FindRecordLabels implements FindRecordLabelsInterface
{
    private FindRecordsLabelTranslationsInterface $findRecordLabelTranslations;

    public function __construct(FindRecordsLabelTranslationsInterface $findRecordLabelTranslations)
    {
        $this->findRecordLabelTranslations = $findRecordLabelTranslations;
    }

    public function byReferenceEntityCodeAndRecordCodes(
        string $referenceEntityCode,
        array $recordCodes,
        string $locale
    ): array {
        return $this->findRecordLabelTranslations->find(
            $referenceEntityCode,
            $recordCodes,
            $locale
        );
    }
}

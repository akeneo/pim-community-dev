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

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Platform;

use Akeneo\ReferenceEntity\Application\Record\SearchRecord\SearchRecord;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;

class SearchRecords implements SearchRecordsInterface
{
    public function __construct(
        private SearchRecord $searchRecord
    ) {
    }

    public function search(
        string $referenceEntityCode,
        string $channel,
        string $locale,
        SearchRecordsParameters $searchParameters
    ): SearchRecordsResult {
        $recordQueryFilters = [
            [
                'field' => 'reference_entity',
                'operator' => '=',
                'value' => $referenceEntityCode,
            ],
        ];

        if (null !== $searchParameters->getSearch()) {
            $recordQueryFilters[] = [
                'field' => 'code_label',
                'operator' => '=',
                'value' => $searchParameters->getSearch(),
            ];
        }

        if (null !== $searchParameters->getIncludeCodes()) {
            if (empty($searchParameters->getIncludeCodes())) {
                return new SearchRecordsResult([], 0);
            }

            $recordQueryFilters[] = [
                'field' => 'code',
                'operator' => 'IN',
                'value' => $searchParameters->getIncludeCodes()
            ];
        }

        if (null !== $searchParameters->getExcludeCodes()) {
            $recordQueryFilters[] = [
                'field' => 'code',
                'operator' => 'NOT IN',
                'value' => $searchParameters->getExcludeCodes()
            ];
        }

        $recordQuery = RecordQuery::createFromNormalized([
            'channel' => $channel,
            'locale' => $locale,
            'filters' => $recordQueryFilters,
            'page' => $searchParameters->getPage() - 1,
            'size' => $searchParameters->getLimit()
        ]);

        $searchRecordResult = ($this->searchRecord)($recordQuery);

        return new SearchRecordsResult(
            array_map(
                static fn (RecordItem $recordItem) => new Record($recordItem->code, $recordItem->labels),
                $searchRecordResult->items
            ),
            $searchRecordResult->matchesCount
        );
    }
}

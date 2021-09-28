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
    private SearchRecord $searchRecord;

    public function __construct(SearchRecord $searchRecord)
    {
        $this->searchRecord = $searchRecord;
    }

    public function search(
        string $referenceEntityCode,
        SearchRecordsParameters $searchParameters
    ): SearchRecordsResult {
        $recordQueryFilters = [
            [
                'field' => 'reference_entity',
                'operator' => '=',
                'value' => $referenceEntityCode,
            ],
            [
                'field' => 'code_label',
                'operator' => '=',
                'value' => $searchParameters->getSearch(),
            ]
        ];

        if(!empty($searchParameters->getIncludeCodes())) {
            $recordQueryFilters[] = [
                'field' => 'code',
                'operator' => 'IN',
                'value' => $searchParameters->getIncludeCodes()
            ];
        }

        if(!empty($searchParameters->getExcludeCodes())) {
            $recordQueryFilters[] = [
                'field' => 'code',
                'operator' => 'NOT IN',
                'value' => $searchParameters->getExcludeCodes()
            ];
        }

        $recordQuery = RecordQuery::createFromNormalized([
            'channel' => null,
            'locale' => $searchParameters->getLocale(),
            'filters' => $recordQueryFilters,
            'page' => $searchParameters->getPage(),
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

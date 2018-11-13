<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Http;

use Akeneo\ReferenceEntity\Application\Record\SearchRecord\SearchConnectorRecord;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Limit;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\ConnectorRecord;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Http\Hal\AddHalDownloadLinkToImages;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class GetConnectorRecordsAction
{
    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    /** @var Limit */
    private $limit;

    /** @var SearchConnectorRecord */
    private $searchConnectorRecord;

    /** @var PaginatorInterface */
    private $halPaginator;

    /** @var AddHalDownloadLinkToImages */
    private $addHalLinksToImageValues;

    public function __construct(
        ReferenceEntityExistsInterface $referenceEntityExists,
        SearchConnectorRecord $searchConnectorRecord,
        PaginatorInterface $halPaginator,
        AddHalDownloadLinkToImages $addHalLinksToImageValues,
        int $limit
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->searchConnectorRecord = $searchConnectorRecord;
        $this->limit = new Limit($limit);
        $this->halPaginator = $halPaginator;
        $this->addHalLinksToImageValues = $addHalLinksToImageValues;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(Request $request, string $referenceEntityIdentifier): JsonResponse
    {
        try {
            $searchAfter = $request->get('search_after', null);
            $searchAfterCode = null !== $searchAfter ? RecordCode::fromString($searchAfter) : null;
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
            $recordQuery = RecordQuery::createPaginatedUsingSearchAfter($referenceEntityIdentifier, $searchAfterCode, $this->limit->intValue());
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        if (false === $this->referenceEntityExists->withIdentifier($referenceEntityIdentifier)) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier));
        }

        $records = ($this->searchConnectorRecord)($recordQuery);
        $records = array_map(function (ConnectorRecord $record) {
            return $record->normalize();
        }, $records);

        $records = ($this->addHalLinksToImageValues)($referenceEntityIdentifier, $records);
        $paginatedRecords = $this->paginateRecords($records, $searchAfter, $referenceEntityIdentifier);

        return new JsonResponse($paginatedRecords);
    }

    private function paginateRecords(array $records, ?string $searchAfter, ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $lastRecord = end($records);
        reset($records);
        $lastRecordCode = $lastRecord['code'] ?? null;

        $paginationParameters = [
            'list_route_name'     => 'akeneo_reference_entities_records_rest_connector_get',
            'item_route_name'     => 'akeneo_reference_entities_record_rest_connector_get',
            'search_after'        => [
                'self' => $searchAfter,
                'next' => $lastRecordCode
            ],
            'limit'               => $this->limit->intValue(),
            'item_identifier_key' => 'code',
            'uri_parameters'      => [
                'referenceEntityIdentifier' => (string) $referenceEntityIdentifier
            ],
            'query_parameters'    => [],
        ];

        return $this->halPaginator->paginate($records, $paginationParameters, count($records));
    }
}

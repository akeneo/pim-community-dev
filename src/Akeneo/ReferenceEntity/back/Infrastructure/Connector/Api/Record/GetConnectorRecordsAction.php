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

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record;

use Akeneo\ReferenceEntity\Application\Record\SearchRecord\SearchConnectorRecord;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Limit;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\ConnectorRecord;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\Hal\AddHalDownloadLinkToRecordImages;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    /** @var AddHalDownloadLinkToRecordImages */
    private $addHalLinksToImageValues;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(
        ReferenceEntityExistsInterface $referenceEntityExists,
        SearchConnectorRecord $searchConnectorRecord,
        PaginatorInterface $halPaginator,
        AddHalDownloadLinkToRecordImages $addHalLinksToImageValues,
        int $limit,
        ValidatorInterface $validator
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->searchConnectorRecord = $searchConnectorRecord;
        $this->limit = new Limit($limit);
        $this->halPaginator = $halPaginator;
        $this->addHalLinksToImageValues = $addHalLinksToImageValues;
        $this->validator = $validator;
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
            $channelReferenceValuesFilter = ChannelReference::createfromNormalized($request->get('channel', null));
            $localeIdentifiersValuesFilter = $this->getLocaleIdentifiersValuesFilterFromRequest($request);
            $updatedSinceFilter = $this->getUpdatedSinceFilterFromRequest($request);
            $recordQuery = RecordQuery::createPaginatedQueryUsingSearchAfter(
                $referenceEntityIdentifier,
                $channelReferenceValuesFilter,
                $localeIdentifiersValuesFilter,
                $this->limit->intValue(),
                $searchAfterCode,
                $updatedSinceFilter
            );
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        // Make an updatedat validator that accepts all the filters and checks the date format
        // RecordQuery will accept a string
        // Put the dateTime in elasticsearch in FindIdentifiersForQuery
        $violations = $this->validator->validate($recordQuery);
        if ($violations->count() > 0) {
            throw new ViolationHttpException($violations, 'Invalid query parameters');
        }

        if (false === $this->referenceEntityExists->withIdentifier($referenceEntityIdentifier)) {
            throw new NotFoundHttpException(sprintf('Reference entity "%s" does not exist.', $referenceEntityIdentifier));
        }

        $records = ($this->searchConnectorRecord)($recordQuery);
        $records = array_map(function (ConnectorRecord $record) {
            return $record->normalize();
        }, $records);

        $records = ($this->addHalLinksToImageValues)($referenceEntityIdentifier, $records);
        $paginatedRecords = $this->paginateRecords($records, $request, $referenceEntityIdentifier);

        return new JsonResponse($paginatedRecords);
    }

    private function paginateRecords(array $records, Request $request, ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $lastRecord = end($records);
        reset($records);
        $lastRecordCode = $lastRecord['code'] ?? null;

        $paginationParameters = [
            'list_route_name'     => 'akeneo_reference_entities_records_rest_connector_get',
            'item_route_name'     => 'akeneo_reference_entities_record_rest_connector_get',
            'search_after'        => [
                'self' => $request->get('search_after', null),
                'next' => $lastRecordCode
            ],
            'limit'               => $this->limit->intValue(),
            'item_identifier_key' => 'code',
            'uri_parameters'      => [
                'referenceEntityIdentifier' => (string) $referenceEntityIdentifier,
            ],
            'query_parameters'    => [
                'channel' => $request->get('channel', null),
                'locales' => $request->get('locales', null),
            ],
        ];

        return $this->halPaginator->paginate($records, $paginationParameters, count($records));
    }

    private function getLocaleIdentifiersValuesFilterFromRequest(Request $request): LocaleIdentifierCollection
    {
        $locales = $request->get('locales', '');
        $locales = '' === $locales ? [] : explode(',', $locales);


        return LocaleIdentifierCollection::fromNormalized($locales);
    }

    private function getUpdatedSinceFilterFromRequest(Request $request): ?array
    {
        $searchQuery = $request->get('search', '');

        if (null == $searchQuery) {
            return null;
        }

        $search = json_decode($searchQuery, true);
        $updatedSince = array_merge($search['updated'][0], ['field' => 'updated']);

        return $updatedSince;
    }
}

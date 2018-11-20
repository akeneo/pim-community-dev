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

use Akeneo\ReferenceEntity\Application\ReferenceEntity\SearchReferenceEntity\SearchConnectorReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Limit;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\ConnectorReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityQuery;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Http\Hal\AddHalDownloadLinkToReferenceEntityImage;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class GetConnectorReferenceEntitiesAction
{
    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    /** @var Limit */
    private $limit;

    /** @var SearchConnectorReferenceEntity */
    private $searchConnectorReferenceEntity;

    /** @var PaginatorInterface */
    private $halPaginator;

    /** @var AddHalDownloadLinkToReferenceEntityImage */
    private $addHalLinksToImageValues;

    public function __construct(
        ReferenceEntityExistsInterface $referenceEntityExists,
        SearchConnectorReferenceEntity $searchConnectorReferenceEntity,
        PaginatorInterface $halPaginator,
        AddHalDownloadLinkToReferenceEntityImage $addHalLinksToImageValues,
        int $limit
    ) {
        $this->referenceEntityExists = $referenceEntityExists;
        $this->searchConnectorReferenceEntity = $searchConnectorReferenceEntity;
        $this->limit = new Limit($limit);
        $this->halPaginator = $halPaginator;
        $this->addHalLinksToImageValues = $addHalLinksToImageValues;
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $searchAfter = $request->get('search_after', null);
            $searchAfterIdentifier = null !== $searchAfter ? ReferenceEntityIdentifier::fromString($searchAfter) : null;
            $referenceEntityQuery = ReferenceEntityQuery::createPaginatedUsingSearchAfter($this->limit->intValue(), $searchAfterIdentifier);
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $referenceEntities = ($this->searchConnectorReferenceEntity)($referenceEntityQuery);
        $referenceEntities = array_map(function (ConnectorReferenceEntity $referenceEntity) {
            $normalizedReferenceEntity = $referenceEntity->normalize();
            return ($this->addHalLinksToImageValues)($normalizedReferenceEntity);
        }, $referenceEntities);

        $paginatedRecords = $this->paginateReferenceEntities($referenceEntities, $searchAfter);

        return new JsonResponse($paginatedRecords);
    }

    private function paginateReferenceEntities(array $records, ?string $searchAfter): array
    {
        $lastRecord = end($records);
        reset($records);
        $lastRecordCode = $lastRecord['code'] ?? null;

        $paginationParameters = [
            'list_route_name'     => 'akeneo_reference_entities_reference_entities_rest_connector_get',
            'item_route_name'     => 'akeneo_reference_entities_reference_entity_rest_connector_get',
            'search_after'        => [
                'self' => $searchAfter,
                'next' => $lastRecordCode
            ],
            'limit'               => $this->limit->intValue(),
            'item_identifier_key' => 'code',
            'query_parameters'    => [],
        ];

        return $this->halPaginator->paginate($records, $paginationParameters, count($records));
    }
}

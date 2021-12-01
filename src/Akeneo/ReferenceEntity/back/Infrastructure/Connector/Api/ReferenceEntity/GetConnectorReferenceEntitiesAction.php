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

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Limit;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\ConnectorReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\FindConnectorReferenceEntityItemsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityQuery;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\ReferenceEntity\Hal\AddHalDownloadLinkToReferenceEntityImage;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class GetConnectorReferenceEntitiesAction
{
    private Limit $limit;

    public function __construct(
        private FindConnectorReferenceEntityItemsInterface $findConnectorReferenceEntityItems,
        private PaginatorInterface $halPaginator,
        private AddHalDownloadLinkToReferenceEntityImage $addHalDownloadLinkToImage,
        int $limit,
        private SecurityFacade $securityFacade
    ) {
        $this->limit = new Limit($limit);
    }

    /**
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function __invoke(Request $request): JsonResponse
    {
        $this->denyAccessUnlessAclIsGranted();
        try {
            $searchAfter = $request->get('search_after', null);
            $searchAfterIdentifier = null !== $searchAfter ? ReferenceEntityIdentifier::fromString($searchAfter) : null;
            $referenceEntityQuery = ReferenceEntityQuery::createPaginatedQuery($this->limit->intValue(), $searchAfterIdentifier);
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        $referenceEntities = $this->findConnectorReferenceEntityItems->find($referenceEntityQuery);
        $referenceEntities = array_map(function (ConnectorReferenceEntity $referenceEntity) {
            $normalizedReferenceEntity = $referenceEntity->normalize();
            return ($this->addHalDownloadLinkToImage)($normalizedReferenceEntity);
        }, $referenceEntities);

        $paginatedReferenceEntities = $this->paginateReferenceEntities($referenceEntities, $searchAfter);

        return new JsonResponse($paginatedReferenceEntities);
    }

    private function paginateReferenceEntities(array $referenceEntities, ?string $searchAfter): array
    {
        $lastReferenceEntity = end($referenceEntities);
        reset($referenceEntities);
        $lastReferenceEntityCode = $lastReferenceEntity['code'] ?? null;

        $paginationParameters = [
            'list_route_name'     => 'akeneo_reference_entities_reference_entities_rest_connector_get',
            'item_route_name'     => 'akeneo_reference_entities_reference_entity_rest_connector_get',
            'search_after'        => [
                'self' => $searchAfter,
                'next' => $lastReferenceEntityCode
            ],
            'limit'               => $this->limit->intValue(),
            'item_identifier_key' => 'code',
            'query_parameters'    => [],
        ];

        return $this->halPaginator->paginate($referenceEntities, $paginationParameters, count($referenceEntities));
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        if (!$this->securityFacade->isGranted('pim_api_reference_entity_list')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to list reference entities.');
        }
    }
}

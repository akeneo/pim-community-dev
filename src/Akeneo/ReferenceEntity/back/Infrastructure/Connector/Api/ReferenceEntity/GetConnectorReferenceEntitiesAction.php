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
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class GetConnectorReferenceEntitiesAction
{
    private Limit $limit;

    private FindConnectorReferenceEntityItemsInterface $findConnectorReferenceEntityItems;

    private PaginatorInterface $halPaginator;

    private AddHalDownloadLinkToReferenceEntityImage $addHalDownloadLinkToImage;

    private SecurityFacade $securityFacade;

    private TokenStorageInterface $tokenStorage;

    private LoggerInterface $apiAclLogger;

    public function __construct(
        FindConnectorReferenceEntityItemsInterface $findConnectorReferenceEntityItems,
        PaginatorInterface $halPaginator,
        AddHalDownloadLinkToReferenceEntityImage $addHalDownloadLinkToImage,
        int $limit,
        SecurityFacade $securityFacade,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $apiAclLogger
    ) {
        $this->findConnectorReferenceEntityItems = $findConnectorReferenceEntityItems;
        $this->limit = new Limit($limit);
        $this->halPaginator = $halPaginator;
        $this->addHalDownloadLinkToImage = $addHalDownloadLinkToImage;
        $this->securityFacade = $securityFacade;
        $this->tokenStorage = $tokenStorage;
        $this->apiAclLogger = $apiAclLogger;
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
        $acl = 'pim_api_reference_entity_list';

        if (!$this->securityFacade->isGranted($acl)) {
            $token = $this->tokenStorage->getToken();
            if (null === $token) {
                throw new \LogicException('An user must be authenticated if ACLs are required');
            }

            $user = $token->getUser();
            if (!$user instanceof UserInterface) {
                throw new \LogicException(sprintf(
                    'An instance of "%s" is expected if ACLs are required',
                    UserInterface::class
                ));
            }

            $this->apiAclLogger->warning(sprintf(
                'User "%s" with roles %s is not granted "%s"',
                $user->getUsername(),
                implode(',', $user->getRoles()),
                $acl
            ));
        }
    }
}

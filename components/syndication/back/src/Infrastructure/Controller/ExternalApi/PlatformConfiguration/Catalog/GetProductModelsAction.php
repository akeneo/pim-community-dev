<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Infrastructure\Controller\ExternalApi\PlatformConfiguration\Catalog;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductModelsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductModelsQueryHandler;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ListProductModelsQueryValidator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\GetAssociationTypesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Platform\Syndication\Application\Common\Column\ColumnCollection;
use Akeneo\Platform\Syndication\Application\Common\Source\AssociationTypeSource;
use Akeneo\Platform\Syndication\Application\Common\Source\AttributeSource;
use Akeneo\Platform\Syndication\Application\MapValues\MapValuesQuery;
use Akeneo\Platform\Syndication\Application\MapValues\MapValuesQueryHandler;
use Akeneo\Platform\Syndication\Domain\Query\PlatformConfiguration\FindPlatformConfigurationQueryInterface;
use Akeneo\Platform\Syndication\Infrastructure\Hydrator\ColumnCollectionHydrator;
use Akeneo\Platform\Syndication\Infrastructure\Hydrator\ValueCollectionHydrator;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\Api\Pagination\PaginationTypes;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Elasticsearch\Common\Exceptions\ServerErrorResponseException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

class GetProductModelsAction
{
    private ListProductModelsQueryValidator $listProductModelsQueryValidator;
    private array $apiConfiguration;
    private TokenStorageInterface $tokenStorage;
    private ListProductModelsQueryHandler $listProductModelsQueryHandler;
    private LoggerInterface $apiProductModelAclLogger;
    private SecurityFacade $security;
    private GetAttributes $getAttributes;
    private GetAssociationTypesInterface $getAssociationTypes;
    private ValueCollectionHydrator $valueCollectionHydrator;
    private ColumnCollectionHydrator $columnCollectionHydrator;
    private MapValuesQueryHandler $mapValuesQueryHandler;
    private FindPlatformConfigurationQueryInterface $findPlatformConfigurationQuery;

    public function __construct(
        ListProductModelsQueryValidator $listProductModelsQueryValidator,
        array $apiConfiguration,
        ListProductModelsQueryHandler $listProductModelsQueryHandler,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $apiProductModelAclLogger,
        SecurityFacade $security,
        GetAttributes $getAttributes,
        GetAssociationTypesInterface $getAssociationTypes,
        ValueCollectionHydrator $valueCollectionHydrator,
        ColumnCollectionHydrator $columnCollectionHydrator,
        MapValuesQueryHandler $mapValuesQueryHandler,
        FindPlatformConfigurationQueryInterface $findPlatformConfigurationQuery
    ) {
        $this->apiConfiguration = $apiConfiguration;
        $this->listProductModelsQueryValidator = $listProductModelsQueryValidator;
        $this->listProductModelsQueryHandler = $listProductModelsQueryHandler;
        $this->tokenStorage = $tokenStorage;
        $this->apiProductModelAclLogger = $apiProductModelAclLogger;
        $this->security = $security;
        $this->getAttributes = $getAttributes;
        $this->getAssociationTypes = $getAssociationTypes;
        $this->valueCollectionHydrator = $valueCollectionHydrator;
        $this->columnCollectionHydrator = $columnCollectionHydrator;
        $this->mapValuesQueryHandler = $mapValuesQueryHandler;
        $this->findPlatformConfigurationQuery = $findPlatformConfigurationQuery;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws ServerErrorResponseException
     * @throws UnprocessableEntityHttpException
     */
    public function listAction(Request $request, string $platformConfigurationCode, string $catalogCode): JsonResponse
    {
        $this->denyAccessUnlessAclIsGranted('pim_api_product_list');

        if (null === $request->query->get('identifiers') || '' === $request->query->get('identifiers')) {
            throw new BadRequestHttpException('The query parameter "identifiers" is required.');
        }

        $identifiers = explode(',', $request->query->get('identifiers'));

        $query = new ListProductModelsQuery();
        $query->search = ['identifier' => [['operator' => 'IN', 'value' => $identifiers]]];
        $query->limit = $request->query->get('limit', $this->apiConfiguration['pagination']['limit_by_default']);
        $query->paginationType = PaginationTypes::SEARCH_AFTER;
        $query->withCount = $request->query->get('with_count', 'false');
        $query->searchAfter = $request->query->get('search_after', null);
        $query->userId = $this->getUser()->getId();

        $catalog = $this->getCatalog($platformConfigurationCode, $catalogCode);
        $columnCollection = $this->getColumnCollection($catalog);

        try {
            $this->listProductModelsQueryValidator->validate($query);
            $productModels = $this->listProductModelsQueryHandler->handle($query); // in try block as PQB is doing validation also
        } catch (InvalidQueryException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        } catch (BadRequest400Exception $e) {
            throw new ServerErrorResponseException($e->getMessage(), $e->getCode(), $e);
        }

        return new JsonResponse([
            'products' => $this->normalizeProductsList($productModels, $query, $columnCollection, $catalog),
            'family' => $catalog['code'], //TODO: will be changed when we will have multiple families per job configuration
        ]);
    }

    private function normalizeProductsList(ConnectorProductModelList $connectorProductList, ListProductModelsQuery $query, ColumnCollection $columnCollection, array $catalog): array
    {
        $queryParameters = [
            'with_count' => $query->withCount,
            'pagination_type' => $query->paginationType,
            'limit' => $query->limit,
        ];

        if ($query->search !== []) {
            $queryParameters['search'] = json_encode($query->search);
        }

        $connectorProductModels = $connectorProductList->connectorProductModels();

        $mappedProducts = array_map(function (ConnectorProductModel $connectorProduct) use ($columnCollection) {
            $valueCollection = $this->valueCollectionHydrator->hydrate($connectorProduct, $columnCollection);

            $mapValuesQuery = new MapValuesQuery($columnCollection, $valueCollection);
            $values = $this->mapValuesQueryHandler->handle($mapValuesQuery);

            return [
                'identifier' => $connectorProduct->code(),
                'type' => 'parent',
                'values' => $values,
                'rootParentCode' => $connectorProduct->parentCode(),
                'uuid' => null
            ];
        }, $connectorProductModels);

        return $mappedProducts;
    }

    private function getCatalog(string $platformConfigurationCode, string $catalogCode): array
    {
        try {
            $platformConfiguration = $this->findPlatformConfigurationQuery->execute($platformConfigurationCode);
        } catch (\Exception $e) {
            throw new HttpException(404, 'Platform configuration not found');
        }
        try {
            $catalog = $platformConfiguration->getCatalog($catalogCode);
        } catch (\Exception $e) {
            throw new HttpException(404, 'Catalog projection not found');
        }

        return $catalog;
    }

    private function getColumnCollection(array $catalog): ColumnCollection
    {
        $columns = $catalog['dataMappings'];
        $indexedAttributes = $this->getIndexedAttributes($columns);
        $indexedAssociationTypes = $this->getIndexedAssociationTypes($columns);

        return $this->columnCollectionHydrator->hydrate($columns, $indexedAttributes, $indexedAssociationTypes);
    }

    private function denyAccessUnlessAclIsGranted(string $acl): void
    {
        if (!$this->security->isGranted($acl)) {
            $user = $this->getUser();
            $this->apiProductModelAclLogger->warning(sprintf(
                'User "%s" with roles %s is not granted "%s"',
                $user->getUsername(),
                implode(',', $user->getRoles()),
                $acl
            ));

            throw new AccessDeniedHttpException($this->deniedAccessMessage($acl));
        }
    }

    private function deniedAccessMessage(string $acl): string
    {
        switch ($acl) {
            case 'pim_api_product_list':
                return 'Access forbidden. You are not allowed to list productModels.';
            case 'pim_api_product_edit':
                return 'Access forbidden. You are not allowed to create or update productModels.';
            case 'pim_api_product_remove':
                return 'Access forbidden. You are not allowed to delete productModels.';
            default:
                return 'Access forbidden.';
        }
    }

    private function getIndexedAttributes(array $columns): array
    {
        $attributeCodes = [];
        foreach ($columns as $column) {
            foreach ($column['sources'] as $source) {
                if (AttributeSource::TYPE === $source['type']) {
                    $attributeCodes[] = $source['code'];
                }
            }
        }

        return array_filter($this->getAttributes->forCodes(array_unique($attributeCodes)));
    }

    private function getIndexedAssociationTypes(array $columns): array
    {
        $associationTypeCodes = [];
        foreach ($columns as $column) {
            foreach ($column['sources'] as $source) {
                if (AssociationTypeSource::TYPE === $source['type']) {
                    $associationTypeCodes[] = $source['code'];
                }
            }
        }

        $indexedAssociationTypes = $this->getAssociationTypes->forCodes(array_unique($associationTypeCodes));

        return array_filter($indexedAssociationTypes);
    }

    private function getUser(): UserInterface
    {
        $user = $this->tokenStorage->getToken()->getUser();
        Assert::isInstanceOf($user, UserInterface::class);

        /** @phpstan-ignore-next-line */
        return $user;
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Infrastructure\Controller\ExternalApi\PlatformConfiguration\Catalog;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductModelsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ListProductModelsQueryValidator;
use Akeneo\Platform\Syndication\Domain\Query\PlatformConfiguration\FindPlatformConfigurationQueryInterface;
use Akeneo\Platform\Syndication\Infrastructure\UseCases\ListProductModelIdsQueryHandler;
use Akeneo\Tool\Bundle\ApiBundle\Documentation;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
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
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

class GetProductModelIdentifiersAction
{
    private const PAGE_SIZE = 1000;
    protected array $apiConfiguration;

    /** @phpstan-ignore-next-line */
    private ListProductModelsQueryValidator $listProductsQueryValidator;
    private ListProductModelIdsQueryHandler $listProductIdsQueryHandler;
    private TokenStorageInterface $tokenStorage;
    private LoggerInterface $apiProductAclLogger;
    private SecurityFacade $security;
    private FindPlatformConfigurationQueryInterface $findPlatformConfigurationQuery;

    public function __construct(
        array $apiConfiguration,
        ListProductModelsQueryValidator $listProductsQueryValidator,
        ListProductModelIdsQueryHandler $listProductIdsQueryHandler,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $apiProductAclLogger,
        SecurityFacade $security,
        FindPlatformConfigurationQueryInterface $findPlatformConfigurationQuery
    ) {
        $this->apiConfiguration = $apiConfiguration;
        $this->listProductsQueryValidator = $listProductsQueryValidator;
        $this->listProductIdsQueryHandler = $listProductIdsQueryHandler;
        $this->tokenStorage = $tokenStorage;
        $this->apiProductAclLogger = $apiProductAclLogger;
        $this->security = $security;
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

        $query = new ListProductModelsQuery();

        $query->search = $this->generateQuerySearch($platformConfigurationCode, $catalogCode);


        $query->channelCode = null;
        $query->limit = 1000;
        $query->paginationType = PaginationTypes::SEARCH_AFTER;
        $query->searchLocaleCode = null;
        $query->searchAfter = $request->query->get('search_after', null);
        $query->userId = $this->getUser()->getId();

        try {
            // $this->listProductsQueryValidator->validate($query); // TODO: Create our own but with no size limit
            $productIdentifiers = $this->listProductIdsQueryHandler->handle($query); // in try block as PQB is doing validation also
        } catch (InvalidQueryException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        } catch (BadRequest400Exception $e) {
            $message = json_decode($e->getMessage(), true);
            if (
                null !== $message && isset($message['error']['root_cause'][0]['type'])
                && 'illegal_argument_exception' === $message['error']['root_cause'][0]['type']
                && 0 === strpos($message['error']['root_cause'][0]['reason'], 'Result window is too large, from + size must be less than or equal to:')
            ) {
                throw new DocumentedHttpException(
                    Documentation::URL_DOCUMENTATION . 'pagination.html#search-after-type',
                    /** @phpstan-ignore-next-line */
                    'You have reached the maximum number of pages you can retrieve with the "page" pagination type. Please use the search after pagination type instead',
                    $e
                );
            }

            throw new ServerErrorResponseException($e->getMessage(), $e->getCode(), $e);
        }

        return new JsonResponse([
            'type' => 'product_model',
            'total_count' => $productIdentifiers->totalNumberOfProductModelIdentifiers(),
            'has_next' => count($productIdentifiers->productModelIdentifiers()) === self::PAGE_SIZE,
            'identifiers' => array_filter($productIdentifiers->productModelIdentifiers(), function ($identifier) use ($query) {
                return $query->searchAfter !== $identifier;
            }),
        ]);
    }

    private function denyAccessUnlessAclIsGranted(string $acl): void
    {
        if (!$this->security->isGranted($acl)) {
            $user = $this->getUser();

            $this->apiProductAclLogger->warning(sprintf(
                'User "%s" with roles %s is not granted "%s"',
                $user->getUserIdentifier(),
                implode(',', $user->getRoles()),
                $acl
            ));

            throw new AccessDeniedHttpException($this->deniedAccessMessage($acl));
        }
    }

    private function generateQuerySearch(string $platformConfigurationCode, string $catalogCode): array
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

        $filters = [...$catalog['filters'], ['field' => 'parent', 'operator' => 'EMPTY']];

        return array_reduce($filters, function (array $accumulator, array $filter) {
            $locale = isset($filter['context']['locale']) ? $filter['context']['locale'] : null;
            $locales = isset($filter['context']['locales']) ? $filter['context']['locales'] : null;
            $scope = isset($filter['context']['channel']) ? $filter['context']['channel'] : null;

            return array_merge(
                $accumulator,
                [$filter['field'] => [array_filter([
                    'operator' => $filter['operator'],
                    'value' => $filter['value'] ?? '',
                    'locale' => $locale,
                    'locales' => $locales,
                    'scope' => $scope,
                ])]],
            );
        }, []);
    }

    private function deniedAccessMessage(string $acl): string
    {
        switch ($acl) {
            case 'pim_api_product_list':
                return 'Access forbidden. You are not allowed to list products.';
            case 'pim_api_product_edit':
                return 'Access forbidden. You are not allowed to create or update products.';
            case 'pim_api_product_remove':
                return 'Access forbidden. You are not allowed to delete products.';
            default:
                return 'Access forbidden.';
        }
    }

    private function getUser(): UserInterface
    {
        $user = $this->tokenStorage->getToken()->getUser();
        Assert::isInstanceOf($user, UserInterface::class);

        return $user;
    }
}

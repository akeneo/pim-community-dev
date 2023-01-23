<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Infrastructure\Security\DenyAccessUnlessGrantedTrait;
use Akeneo\Catalogs\Infrastructure\Security\GetCurrentUsernameTrait;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogDisabledException;
use Akeneo\Catalogs\ServiceAPI\Exception\ProductSchemaMappingNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\Catalogs\ServiceAPI\Query\GetMappedProductsQuery;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type MappedProduct from GetMappedProductsQuery
 */
final class GetMappedProductsAction
{
    use DenyAccessUnlessGrantedTrait;
    use GetCurrentUsernameTrait;

    public function __construct(
        private QueryBus $queryBus,
        private TokenStorageInterface $tokenStorage,
        private SecurityFacadeInterface $security,
        private RouterInterface $router,
    ) {
    }

    public function __invoke(Request $request, string $catalogId): Response
    {
        $this->denyAccessUnlessGrantedToListCatalogs();
        $this->denyAccessUnlessGrantedToListProducts();

        $catalog = $this->getCatalog($catalogId);

        $this->denyAccessUnlessOwnerOfCatalog($catalog, $this->getCurrentUsername());

        [$searchAfter, $limit, $updatedAfter, $updatedBefore] = $this->getParameters($request);

        try {
            $mappedProducts = $this->queryBus->execute(new GetMappedProductsQuery($catalogId, $searchAfter, $limit, $updatedAfter, $updatedBefore));
        } catch (ValidationFailedException $e) {
            throw new ViolationHttpException($e->getViolations());
        } catch (CatalogDisabledException) {
            return new JsonResponse(
                [
                    'error' => \sprintf(
                        'No products to synchronize. The catalog %s has been disabled on the PIM side.' .
                        ' Note that you can get catalogs status with the GET /api/rest/v1/catalogs endpoint.',
                        $catalog->getId(),
                    ),
                ],
                Response::HTTP_OK,
            );
        } catch (ProductSchemaMappingNotFoundException) {
            return new JsonResponse(
                [
                    'error' => 'Impossible to map products: no product mapping schema available for this catalog.',
                ],
                Response::HTTP_OK,
            );
        }

        return new JsonResponse($this->paginate($catalog, $mappedProducts, $searchAfter, $limit, $updatedAfter, $updatedBefore), Response::HTTP_OK);
    }

    /**
     * @return array{string|null, int, string|null, string|null}
     */
    private function getParameters(Request $request): array
    {
        $searchAfter = $request->query->get('search_after');
        $limit = (int) $request->query->get('limit', 100);
        $updatedAfter = $request->query->get('updated_after');
        $updatedBefore = $request->query->get('updated_before');

        if (null !== $searchAfter && !\is_string($searchAfter)) {
            throw new BadRequestHttpException();
        }

        if (null !== $updatedAfter && !\is_string($updatedAfter)) {
            throw new BadRequestHttpException();
        }

        if (null !== $updatedBefore && !\is_string($updatedBefore)) {
            throw new BadRequestHttpException();
        }

        return [$searchAfter, $limit, $updatedAfter, $updatedBefore];
    }

    private function getCatalog(string $id): Catalog
    {
        try {
            $catalog = $this->queryBus->execute(new GetCatalogQuery($id));
        } catch (ValidationFailedException $e) {
            throw new NotFoundHttpException(\sprintf('Catalog "%s" does not exist or you can\'t access it.', $id), $e);
        }

        if (null === $catalog) {
            throw new NotFoundHttpException(\sprintf('Catalog "%s" does not exist or you can\'t access it.', $id));
        }

        return $catalog;
    }

    /**
     * @param array<MappedProduct> $mappedProducts
     *
     * @return array{_links: array{self: array{href: string}, first: array{href: string}, next?: array{href: string}}, _embedded: array{items: array<MappedProduct>}}
     */
    private function paginate(Catalog $catalog, array $mappedProducts, ?string $searchAfter, int $limit, ?string $updatedAfter, ?string $updatedBefore): array
    {
        $last = \end($mappedProducts);

        $result = [
            '_links' => [
                'self' => [
                    'href' => $this->router->generate('akeneo_catalogs_public_get_mapped_products', [
                        'catalogId' => $catalog->getId(),
                        'search_after' => $searchAfter,
                        'limit' => $limit,
                        'updated_after' => $updatedAfter,
                        'updated_before' => $updatedBefore,
                    ], RouterInterface::ABSOLUTE_URL),
                ],
                'first' => [
                    'href' => $this->router->generate('akeneo_catalogs_public_get_mapped_products', [
                        'catalogId' => $catalog->getId(),
                        'limit' => $limit,
                        'updated_after' => $updatedAfter,
                        'updated_before' => $updatedBefore,
                    ], RouterInterface::ABSOLUTE_URL),
                ],
            ],
            '_embedded' => [
                'items' => $mappedProducts,
            ],
        ];

        if (false !== $last && \count($mappedProducts) >= $limit) {
            $result['_links']['next'] = [
                'href' => $this->router->generate('akeneo_catalogs_public_get_mapped_products', [
                    'catalogId' => $catalog->getId(),
                    'search_after' => $last['uuid'],
                    'limit' => $limit,
                    'updated_after' => $updatedAfter,
                    'updated_before' => $updatedBefore,
                ], RouterInterface::ABSOLUTE_URL),
            ];
        }

        return $result;
    }
}

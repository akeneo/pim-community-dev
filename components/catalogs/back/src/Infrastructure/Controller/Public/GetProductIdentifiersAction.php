<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Infrastructure\Security\DenyAccessUnlessGrantedTrait;
use Akeneo\Catalogs\Infrastructure\Security\GetCurrentUsernameTrait;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductIdentifiersQuery;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
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
 */
class GetProductIdentifiersAction
{
    use GetCurrentUsernameTrait;
    use DenyAccessUnlessGrantedTrait;

    public function __construct(
        private QueryBus $queryBus,
        private TokenStorageInterface $tokenStorage,
        private SecurityFacadeInterface $security,
        private RouterInterface $router,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $this->denyAccessUnlessGrantedToListCatalogs();
        $this->denyAccessUnlessGrantedToListProducts();

        $catalog = $this->getCatalog($id);

        $this->denyAccessUnlessOwnerOfCatalog($catalog, $this->getCurrentUsername());

        [$searchAfter, $limit] = $this->getParameters($request);
        $uuids = $this->getProductUuids($catalog, $searchAfter, $limit);

        return new JsonResponse($this->paginate($catalog, $uuids, $searchAfter, $limit), Response::HTTP_OK);
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
     * @return array{string|null, int}
     */
    private function getParameters(Request $request): array
    {
        $searchAfter = $request->query->get('search_after');
        $limit = (int) $request->query->get('limit', 100);

        if (null !== $searchAfter && !\is_string($searchAfter)) {
            throw new BadRequestHttpException();
        }

        return [$searchAfter, $limit];
    }

    /**
     * @return array<string>
     */
    private function getProductUuids(Catalog $catalog, ?string $searchAfter, int $limit): array
    {
        if (!$catalog->isEnabled()) {
            return [];
        }

        try {
            return $this->queryBus->execute(new GetProductIdentifiersQuery(
                $catalog->getId(),
                $searchAfter,
                $limit,
            ));
        } catch (ValidationFailedException $e) {
            throw new BadRequestHttpException();
        }
    }

    /**
     * @param array<string> $uuids
     * @return array<array-key, mixed>
     */
    private function paginate(Catalog $catalog, array $uuids, ?string $searchAfter, int $limit): array
    {
        $last = \end($uuids);

        $result = [
            '_links' => [
                'self' => [
                    'href' => $this->router->generate('akeneo_catalogs_public_get_product_identifiers', [
                        'id' => $catalog->getId(),
                        'search_after' => $searchAfter,
                        'limit' => $limit,
                    ]),
                ],
                'first' => [
                    'href' => $this->router->generate('akeneo_catalogs_public_get_product_identifiers', [
                        'id' => $catalog->getId(),
                        'limit' => $limit,
                    ]),
                ],
            ],
            '_embedded' => [
                'items' => $uuids,
            ],
        ];

        if (\count($uuids) >= $limit) {
            $result['_links']['next'] = [
                'href' => $this->router->generate('akeneo_catalogs_public_get_product_identifiers', [
                    'id' => $catalog->getId(),
                    'search_after' => $last,
                    'limit' => $limit,
                ]),
            ];
        }

        return $result;
    }
}

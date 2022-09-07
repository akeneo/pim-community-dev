<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Infrastructure\Security\DenyAccessUnlessGrantedTrait;
use Akeneo\Catalogs\Infrastructure\Security\GetCurrentUsernameTrait;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductUuidsQuery;
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
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductUuidsAction
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

        [$searchAfter, $limit, $updatedBefore, $updatedAfter] = $this->getParameters($request);
        $uuids = $this->getProductUuids($catalog, $searchAfter, $limit, $updatedBefore, $updatedAfter);

        return new JsonResponse($this->paginate($catalog, $uuids, $searchAfter, $limit, $updatedBefore, $updatedAfter), Response::HTTP_OK);
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
     * @return array{string|null, int, string|null, string|null}
     */
    private function getParameters(Request $request): array
    {
        $searchAfter = $request->query->get('search_after');
        $limit = (int) $request->query->get('limit', 100);
        $updatedBefore = $request->query->get('updated_before');
        $updatedAfter = $request->query->get('updated_after');

        if (null !== $searchAfter && !\is_string($searchAfter)) {
            throw new BadRequestHttpException();
        }

        if (null !== $updatedBefore && !\is_string($updatedBefore)) {
            throw new BadRequestHttpException();
        }

        if (null !== $updatedAfter && !\is_string($updatedAfter)) {
            throw new BadRequestHttpException();
        }

        return [$searchAfter, $limit, $updatedBefore, $updatedAfter];
    }

    /**
     * @return array<string>
     */
    private function getProductUuids(Catalog $catalog, ?string $searchAfter, int $limit, ?string $updatedBefore, ?string $updatedAfter): array
    {
        if (!$catalog->isEnabled()) {
            return [];
        }

        try {
            return $this->queryBus->execute(new GetProductUuidsQuery(
                $catalog->getId(),
                $searchAfter,
                $limit,
                $updatedBefore,
                $updatedAfter,
            ));
        } catch (ValidationFailedException $e) {
            throw new ViolationHttpException($e->getViolations());
        }
    }

    /**
     * @param array<string> $uuids
     *
     * @return array{_links: array{self: array{href: string}, first: array{href: string}, next?: array{href: string}}, _embedded: array{items: string[]}}
     */
    private function paginate(Catalog $catalog, array $uuids, ?string $searchAfter, int $limit, ?string $updatedBefore, ?string $updatedAfter): array
    {
        $last = \end($uuids);

        $result = [
            '_links' => [
                'self' => [
                    'href' => $this->router->generate('akeneo_catalogs_public_get_product_uuids', [
                        'id' => $catalog->getId(),
                        'search_after' => $searchAfter,
                        'limit' => $limit,
                        'updated_before' => $updatedBefore,
                        'updated_after' => $updatedAfter,
                    ]),
                ],
                'first' => [
                    'href' => $this->router->generate('akeneo_catalogs_public_get_product_uuids', [
                        'id' => $catalog->getId(),
                        'limit' => $limit,
                        'updated_before' => $updatedBefore,
                        'updated_after' => $updatedAfter,
                    ]),
                ],
            ],
            '_embedded' => [
                'items' => $uuids,
            ],
        ];

        if (\count($uuids) >= $limit) {
            $result['_links']['next'] = [
                'href' => $this->router->generate('akeneo_catalogs_public_get_product_uuids', [
                    'id' => $catalog->getId(),
                    'search_after' => $last,
                    'limit' => $limit,
                    'updated_before' => $updatedBefore,
                    'updated_after' => $updatedAfter,
                ]),
            ];
        }

        return $result;
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Infrastructure\Security\DenyAccessUnlessGrantedTrait;
use Akeneo\Catalogs\Infrastructure\Security\GetCurrentUsernameTrait;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogDisabledException;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Exception\ProductNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductQuery;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type Product from GetProductQuery
 */
class GetProductAction
{
    use GetCurrentUsernameTrait;
    use DenyAccessUnlessGrantedTrait;

    public function __construct(
        private QueryBus $queryBus,
        private TokenStorageInterface $tokenStorage,
        private SecurityFacadeInterface $security,
    ) {
    }

    public function __invoke(Request $request, string $id, string $uuid): Response
    {
        $this->denyAccessUnlessGrantedToListCatalogs();
        $this->denyAccessUnlessGrantedToListProducts();

        $catalog = $this->getCatalog($id, $uuid);

        $this->denyAccessUnlessOwnerOfCatalog($catalog, $this->getCurrentUsername());

        try {
            $product = $this->getProduct($catalog->getId(), $uuid);
        } catch (CatalogDisabledException) {
            return new JsonResponse([
                'error' => \sprintf('No products to synchronize. The catalog "%s" has been disabled on PIM side. Note that you can get catalogs status with the GET /api/rest/v1/catalogs endpoint.', $id),
            ], Response::HTTP_OK);
        }

        return new JsonResponse($product, Response::HTTP_OK);
    }

    private function getCatalog(string $id, string $productUuid): Catalog
    {
        try {
            $catalog = $this->queryBus->execute(new GetCatalogQuery($id));
        } catch (ValidationFailedException $e) {
            throw new NotFoundHttpException($this->getNotFoundMessage($id, $productUuid), $e);
        }

        if (null === $catalog) {
            throw new NotFoundHttpException($this->getNotFoundMessage($id, $productUuid));
        }

        return $catalog;
    }

    /**
     * @return Product
     * @throws CatalogDisabledException
     * @throws ViolationHttpException
     * @throws NotFoundHttpException
     */
    private function getProduct(string $catalogId, string $productUuid): array
    {
        try {
            $product = $this->queryBus->execute(new GetProductQuery(
                $catalogId,
                $productUuid
            ));
        } catch (ValidationFailedException $e) {
            throw new ViolationHttpException(
                violations: $e->getViolations(),
                previous: $e,
            );
        } catch (CatalogNotFoundException|ProductNotFoundException $notFoundException) {
            throw new NotFoundHttpException(
                message: $this->getNotFoundMessage($catalogId, $productUuid),
                previous: $notFoundException,
            );
        }

        return $product;
    }

    private function getNotFoundMessage(string $catalogId, string $productUuid): string
    {
        return \sprintf('Either catalog "%s" does not exist or you can\'t access it, or product "%s" does not exist or you do not have permission to access it.', $catalogId, $productUuid);
    }
}

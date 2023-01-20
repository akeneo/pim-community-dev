<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Infrastructure\Security\DenyAccessUnlessGrantedTrait;
use Akeneo\Catalogs\Infrastructure\Security\GetCurrentUsernameTrait;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogDisabledException;
use Akeneo\Catalogs\ServiceAPI\Exception\ProductNotFoundException as ServiceApiProductNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Exception\ProductSchemaMappingNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\Catalogs\ServiceAPI\Query\GetMappedProductQuery;
use Akeneo\Catalogs\ServiceAPI\Query\GetMappedProductsQuery;
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
 * @phpstan-import-type MappedProduct from GetMappedProductsQuery
 */
final class GetMappedProductAction
{
    use DenyAccessUnlessGrantedTrait;
    use GetCurrentUsernameTrait;

    public function __construct(
        private QueryBus $queryBus,
        private TokenStorageInterface $tokenStorage,
        private SecurityFacadeInterface $security,
    ) {
    }

    public function __invoke(Request $request, string $catalogId, string $productUuid): Response
    {
        $this->denyAccessUnlessGrantedToListCatalogs();
        $this->denyAccessUnlessGrantedToListProducts();

        $catalog = $this->getCatalog($catalogId, $productUuid);

        $this->denyAccessUnlessOwnerOfCatalog($catalog, $this->getCurrentUsername());

        try {
            $mappedProduct = $this->queryBus->execute(new GetMappedProductQuery($catalogId, $productUuid));
        } catch (ValidationFailedException $e) {
            throw new ViolationHttpException($e->getViolations());
        } catch (CatalogDisabledException) {
            return new JsonResponse(
                [
                    'error' => \sprintf(
                        'No product to synchronize. The catalog %s has been disabled on the PIM side.' .
                        ' Note that you can get catalogs status with the GET /api/rest/v1/catalogs endpoint.',
                        $catalog->getId(),
                    ),
                ],
                Response::HTTP_OK,
            );
        } catch (ProductSchemaMappingNotFoundException) {
            return new JsonResponse(
                [
                    'error' => 'Impossible to map the product: no product mapping schema available for this catalog.',
                ],
                Response::HTTP_OK,
            );
        } catch (ServiceApiProductNotFoundException) {
            throw new NotFoundHttpException(\sprintf('Either catalog "%s" does not exist or you can\'t access it, or product "%s" does not exist or you do not have permission to access it.', $catalogId, $productUuid));
        }

        return new JsonResponse($mappedProduct, Response::HTTP_OK);
    }

    private function getCatalog(string $id, string $productUuid): Catalog
    {
        try {
            $catalog = $this->queryBus->execute(new GetCatalogQuery($id));
        } catch (ValidationFailedException $e) {
            throw new NotFoundHttpException(\sprintf('Either catalog "%s" does not exist or you can\'t access it, or product "%s" does not exist or you do not have permission to access it.', $id, $productUuid), $e);
        }

        if (null === $catalog) {
            throw new NotFoundHttpException(\sprintf('Either catalog "%s" does not exist or you can\'t access it, or product "%s" does not exist or you do not have permission to access it.', $id, $productUuid));
        }

        return $catalog;
    }
}

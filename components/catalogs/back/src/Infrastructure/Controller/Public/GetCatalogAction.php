<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Domain\Query\GetCatalogQuery;
use Akeneo\Catalogs\Infrastructure\Messenger\QueryBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCatalogAction
{
    public function __construct(
        private QueryBus $queryBus,
    ) {
    }

    public function __invoke(string $id): Response
    {
        try {
            $catalog = $this->queryBus->execute(new GetCatalogQuery($id));
        } catch (CatalogNotFoundException $e) {
            throw new NotFoundHttpException('Catalog not found', $e);
        }

        return new JsonResponse($catalog, 200);
    }
}

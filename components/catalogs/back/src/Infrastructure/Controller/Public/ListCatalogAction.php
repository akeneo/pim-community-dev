<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Domain\Model\Catalog;
use Akeneo\Catalogs\Domain\Query\ListCatalogQuery;
use Akeneo\Catalogs\Infrastructure\Messenger\QueryBus;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ListCatalogAction
{
    // You cannot have more than 15 catalogs
    private const LIMIT = 20;

    public function __construct(
        private QueryBus $queryBus,
        private PaginatorInterface $paginator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $page = (int) $request->query->get('page', 1);

        $catalogs = $this->queryBus->execute(new ListCatalogQuery($page));

        return new JsonResponse($this->paginate($catalogs, $page), 200);
    }

    /**
     * @param array<Catalog> $catalogs
     * @return array<array-key, mixed>
     */
    private function paginate(array $catalogs, int $page): array
    {
        $items = \array_map(static fn (Catalog $catalog) => $catalog->jsonSerialize(), $catalogs);

        return $this->paginator->paginate($items, [
            'query_parameters' => [
                'page' => $page,
                'limit' => self::LIMIT,
            ],
            'list_route_name' => 'akeneo_catalogs_public_list_catalog',
            'item_route_name' => 'akeneo_catalogs_public_get_catalog',
            'item_route_parameter' => 'id',
            'item_identifier_key' => 'id',
        ], null);
    }
}

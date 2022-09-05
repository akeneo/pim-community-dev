<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Infrastructure\Security\DenyAccessUnlessGrantedTrait;
use Akeneo\Catalogs\Infrastructure\Security\GetCurrentUsernameTrait;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogsByOwnerUsernameQuery;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\Tool\Component\Api\Pagination\OffsetHalPaginator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCatalogsAction
{
    use GetCurrentUsernameTrait;
    use DenyAccessUnlessGrantedTrait;

    public function __construct(
        private QueryBus $queryBus,
        private NormalizerInterface $normalizer,
        private TokenStorageInterface $tokenStorage,
        private SecurityFacadeInterface $security,
        private OffsetHalPaginator $offsetPaginator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGrantedToListCatalogs();

        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 100);

        $ownerUsername = $this->getCurrentUsername();

        try {
            $catalogs = $this->queryBus->execute(new GetCatalogsByOwnerUsernameQuery($ownerUsername, $page, $limit));
        } catch (ValidationFailedException) {
            throw new BadRequestHttpException();
        }

        return new JsonResponse($this->paginate($catalogs, $page, $limit), Response::HTTP_OK);
    }

    /**
     * @param array<Catalog> $catalogs
     * @return array<array-key, mixed>
     */
    private function paginate(array $catalogs, int $page, int $limit): array
    {
        /** @var array<mixed> $items */
        $items = $this->normalizer->normalize($catalogs, 'public');

        return $this->offsetPaginator->paginate($items, [
            'query_parameters' => [
                'page' => $page,
                'limit' => $limit,
            ],
            'list_route_name' => 'akeneo_catalogs_public_get_catalogs',
            'item_route_name' => 'akeneo_catalogs_public_get_catalog',
            'item_route_parameter' => 'id',
            'item_identifier_key' => 'id',
        ], null);
    }
}

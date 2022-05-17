<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Infrastructure\Security\DenyAccessUnlessGrantedTrait;
use Akeneo\Catalogs\Infrastructure\Security\GetCurrentUserIdTrait;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogsByOwnerIdQuery;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
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
final class GetCatalogsByOwnerIdAction
{
    use GetCurrentUserIdTrait;
    use DenyAccessUnlessGrantedTrait;

    public function __construct(
        private QueryBus $queryBus,
        private NormalizerInterface $normalizer,
        private TokenStorageInterface $tokenStorage,
        private SecurityFacadeInterface $security,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGrantedToListCatalogs();

        $limit = $request->query->get('limit', 100);
        $offset = $request->query->get('offset', 0);

        $ownerId = $this->getCurrentUserId();

        try {
            $catalogs = $this->queryBus->execute(new GetCatalogsByOwnerIdQuery($ownerId, (int) $offset, (int) $limit));
        } catch (ValidationFailedException $e) {
            throw new BadRequestHttpException();
        }

        return new JsonResponse($this->normalizer->normalize($catalogs, 'external_api'), Response::HTTP_OK);
    }
}

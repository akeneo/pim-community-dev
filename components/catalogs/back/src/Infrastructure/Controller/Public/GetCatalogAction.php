<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Infrastructure\Security\DenyAccessUnlessGrantedTrait;
use Akeneo\Catalogs\Infrastructure\Security\GetCurrentUsernameTrait;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCatalogAction
{
    use GetCurrentUsernameTrait;
    use DenyAccessUnlessGrantedTrait;

    public function __construct(
        private QueryBus $queryBus,
        private NormalizerInterface $normalizer,
        private TokenStorageInterface $tokenStorage,
        private SecurityFacadeInterface $security,
    ) {
    }

    public function __invoke(string $id): Response
    {
        $this->denyAccessUnlessGrantedToListCatalogs();

        try {
            $catalog = $this->queryBus->execute(new GetCatalogQuery($id));
        } catch (ValidationFailedException) {
            throw $this->notFound($id);
        }

        $username = $this->getCurrentUsername();
        if (null === $catalog || $catalog->getOwnerUsername() !== $username) {
            throw $this->notFound($id);
        }

        return new JsonResponse($this->normalizer->normalize($catalog, 'public'), Response::HTTP_OK);
    }

    private function notFound(string $id): NotFoundHttpException
    {
        return new NotFoundHttpException(
            \sprintf('Catalog "%s" does not exist or you can\'t access it.', $id),
        );
    }
}

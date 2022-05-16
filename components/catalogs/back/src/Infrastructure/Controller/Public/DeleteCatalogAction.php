<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Infrastructure\Security\DenyAccessUnlessGrantedTrait;
use Akeneo\Catalogs\Infrastructure\Security\GetCurrentUserIdTrait;
use Akeneo\Catalogs\ServiceAPI\Command\DeleteCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
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
final class DeleteCatalogAction
{
    use DenyAccessUnlessGrantedTrait;
    use GetCurrentUserIdTrait;

    public function __construct(
        private CommandBus $commandBus,
        private QueryBus $queryBus,
        private NormalizerInterface $normalizer,
        private TokenStorageInterface $tokenStorage,
        private SecurityFacadeInterface $security,
    ) {
    }

    public function __invoke(string $id): Response
    {
        $this->denyAccessUnlessGrantedToRemoveCatalogs();

        try {
            $catalog = $this->queryBus->execute(new GetCatalogQuery($id));
        } catch (ValidationFailedException $e) {
            throw $this->notFound($id);
        }

        $userId = $this->getCurrentUserId();
        if (null === $catalog || $catalog->getOwnerId() !== $userId) {
            throw $this->notFound($id);
        }

        $this->commandBus->execute(new DeleteCatalogCommand($id));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function notFound(string $id): NotFoundHttpException
    {
        return new NotFoundHttpException(
            \sprintf('Catalog "%s" does not exist or you can\'t access it.', $id)
        );
    }
}

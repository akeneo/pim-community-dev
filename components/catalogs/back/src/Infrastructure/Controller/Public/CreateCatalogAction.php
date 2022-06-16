<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Infrastructure\Security\DenyAccessUnlessGrantedTrait;
use Akeneo\Catalogs\Infrastructure\Security\GetCurrentUsernameTrait;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateCatalogAction
{
    use DenyAccessUnlessGrantedTrait;
    use GetCurrentUsernameTrait;

    public function __construct(
        private CommandBus $commandBus,
        private QueryBus $queryBus,
        private NormalizerInterface $normalizer,
        private TokenStorageInterface $tokenStorage,
        private SecurityFacadeInterface $security,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGrantedToEditCatalogs();

        /** @var array{name?: string} $payload */
        $payload = \json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $catalogId = Uuid::uuid4()->toString();
        $username = $this->getCurrentUsername();

        try {
            $this->commandBus->execute(new CreateCatalogCommand(
                $catalogId,
                $payload['name'] ?? '',
                $username,
            ));
        } catch (ValidationFailedException $e) {
            throw new ViolationHttpException($e->getViolations());
        }

        $catalog = $this->queryBus->execute(new GetCatalogQuery($catalogId));
        if (null === $catalog) {
            throw new \LogicException('The catalog must exist after its creation');
        }

        return new JsonResponse($this->normalizer->normalize($catalog, 'public'), Response::HTTP_CREATED);
    }
}

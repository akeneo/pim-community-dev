<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Public;

use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateCatalogAction
{
    public function __construct(
        private CommandBus $commandBus,
        private QueryBus $queryBus,
        private NormalizerInterface $normalizer,
        private Security $security,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        /** @var array{name?: string} $payload */
        $payload = \json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $id = Uuid::uuid4()->toString();
        $userId = $this->getCurrentUserId();

        try {
            $this->commandBus->execute(new CreateCatalogCommand(
                $id,
                $payload['name'] ?? '',
                $userId,
            ));
        } catch (ValidationFailedException $e) {
            throw new ViolationHttpException($e->getViolations());
        }

        $catalog = $this->queryBus->execute(new GetCatalogQuery($id));

        return new JsonResponse($this->normalizer->normalize($catalog, 'external_api'), 201);
    }

    private function getCurrentUserId(): int
    {
        $user = $this->security->getUser();

        if (null === $user) {
            throw new \LogicException('User should not be null');
        }

        if (!$user instanceof UserInterface) {
            throw new \LogicException(\sprintf('User should be an instance of %s', UserInterface::class));
        }

        return (int) $user->getId();
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Domain\Command\CreateCatalogCommand;
use Akeneo\Catalogs\Domain\Query\GetCatalogQuery;
use Akeneo\Catalogs\Infrastructure\Messenger\CommandBus;
use Akeneo\Catalogs\Infrastructure\Messenger\QueryBus;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
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
    ) {
    }

    public function __invoke(Request $request): Response
    {
        /** @var array{name?: string} $payload */
        $payload = \json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $id = Uuid::uuid4()->toString();

        try {
            $this->commandBus->execute(new CreateCatalogCommand(
                $id,
                $payload['name'] ?? '',
            ));
        } catch (ValidationFailedException $e) {
            throw new ViolationHttpException($e->getViolations());
        }

        $catalog = $this->queryBus->execute(new GetCatalogQuery($id));

        return new JsonResponse($this->normalizer->normalize($catalog, 'external_api'), 201);
    }
}

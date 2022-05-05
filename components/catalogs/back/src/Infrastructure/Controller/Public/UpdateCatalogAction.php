<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Domain\Command\UpdateCatalogCommand;
use Akeneo\Catalogs\Domain\Query\GetCatalogQuery;
use Akeneo\Catalogs\Infrastructure\Messenger\CommandBus;
use Akeneo\Catalogs\Infrastructure\Messenger\QueryBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateCatalogAction
{
    public function __construct(
        private CommandBus $commandBus,
        private QueryBus $queryBus,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        /** @var array{name?: string} $payload */
        $payload = \json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->commandBus->execute(new UpdateCatalogCommand(
            $id,
            $payload['name'] ?? '',
        ));

        $catalog = $this->queryBus->execute(new GetCatalogQuery($id));

        return new JsonResponse($catalog, 200);
    }
}

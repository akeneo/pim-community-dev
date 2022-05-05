<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Domain\Command\DeleteCatalogCommand;
use Akeneo\Catalogs\Infrastructure\Messenger\CommandBus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteCatalogAction
{
    public function __construct(
        private CommandBus $commandBus,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $this->commandBus->execute(new DeleteCatalogCommand(
            $id,
        ));

        return new Response(null, 204);
    }
}

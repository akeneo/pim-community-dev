<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\CommandHandler;

use Akeneo\Catalogs\Application\Persistence\DeleteCatalogQueryInterface;
use Akeneo\Catalogs\Domain\Command\DeleteCatalogCommand;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteCatalogCommandHandler
{
    public function __construct(
        private DeleteCatalogQueryInterface $deleteCatalogQuery,
    ) {
    }

    public function __invoke(DeleteCatalogCommand $command): void
    {
        $this->deleteCatalogQuery->execute($command->getId());
    }
}

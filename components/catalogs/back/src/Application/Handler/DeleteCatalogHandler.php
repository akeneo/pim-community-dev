<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Persistence\Catalog\DeleteCatalogQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Command\DeleteCatalogCommand;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteCatalogHandler
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

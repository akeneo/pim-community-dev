<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\CommandHandler;

use Akeneo\Catalogs\Application\Persistence\UpsertCatalogQueryInterface;
use Akeneo\Catalogs\Domain\Command\UpdateCatalogCommand;
use Akeneo\Catalogs\Domain\Model\Catalog;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateCatalogCommandHandler
{
    public function __construct(
        private UpsertCatalogQueryInterface $upsertCatalogQuery,
    ) {
    }

    public function __invoke(UpdateCatalogCommand $command): void
    {
        $catalog = new Catalog(
            $command->getId(),
            $command->getName(),
        );

        $this->upsertCatalogQuery->execute($catalog);
    }
}

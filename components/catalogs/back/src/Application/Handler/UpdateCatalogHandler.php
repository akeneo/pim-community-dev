<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Persistence\FindOneCatalogByIdQueryInterface;
use Akeneo\Catalogs\Application\Persistence\UpsertCatalogQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Command\UpdateCatalogCommand;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateCatalogHandler
{
    public function __construct(
        private FindOneCatalogByIdQueryInterface $findOneCatalogByIdQuery,
        private UpsertCatalogQueryInterface $upsertCatalogQuery,
    ) {
    }

    public function __invoke(UpdateCatalogCommand $command): void
    {
        $catalog = $this->findOneCatalogByIdQuery->execute($command->getId());

        if (null === $catalog) {
            throw new \LogicException('Catalog must exist');
        }

        $this->upsertCatalogQuery->execute(
            $catalog->getId(),
            $command->getName(),
            $catalog->getOwnerUsername(),
            $catalog->isEnabled(),
        );
    }
}

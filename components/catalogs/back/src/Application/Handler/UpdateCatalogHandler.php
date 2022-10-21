<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\UpsertCatalogQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\ServiceAPI\Command\UpdateCatalogCommand;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateCatalogHandler
{
    public function __construct(
        private GetCatalogQueryInterface $getCatalogQuery,
        private UpsertCatalogQueryInterface $upsertCatalogQuery,
    ) {
    }

    public function __invoke(UpdateCatalogCommand $command): void
    {
        try {
            $catalog = $this->getCatalogQuery->execute($command->getId());
        } catch (CatalogNotFoundException) {
            throw new \LogicException('Catalog must exist');
        }

        $this->upsertCatalogQuery->execute(new Catalog(
            $catalog->getId(),
            $command->getName(),
            $catalog->getOwnerUsername(),
            $catalog->isEnabled(),
            $catalog->getProductSelectionCriteria(),
            $catalog->getProductValueFilters(),
            $catalog->getProductMapping(),
        ));
    }
}

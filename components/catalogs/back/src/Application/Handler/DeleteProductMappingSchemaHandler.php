<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\UpsertCatalogQueryInterface;
use Akeneo\Catalogs\Application\Storage\CatalogsMappingStorageInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\ServiceAPI\Command\DeleteProductMappingSchemaCommand;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogNotFoundException as ServiceApiCatalogNotFoundException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteProductMappingSchemaHandler
{
    public function __construct(
        private GetCatalogQueryInterface $getCatalogQuery,
        private UpsertCatalogQueryInterface $upsertCatalogQuery,
        private CatalogsMappingStorageInterface $catalogsMappingStorage,
    ) {
    }

    public function __invoke(DeleteProductMappingSchemaCommand $command): void
    {
        try {
            $catalog = $this->getCatalogQuery->execute($command->getCatalogId());
        } catch (CatalogNotFoundException) {
            throw new ServiceApiCatalogNotFoundException();
        }

        $this->catalogsMappingStorage->delete(\sprintf('%s_product.json', $catalog->getId()));

        $this->upsertCatalogQuery->execute(new Catalog(
            $catalog->getId(),
            $catalog->getName(),
            $catalog->getOwnerUsername(),
            $catalog->isEnabled(),
            $catalog->getProductSelectionCriteria(),
            $catalog->getProductValueFilters(),
            [],
        ));
    }
}

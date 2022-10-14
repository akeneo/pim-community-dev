<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Storage\MappingStorageInterface;
use Akeneo\Catalogs\ServiceAPI\Command\UpdateCatalogProductMappingSchemaCommand;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateCatalogProductMappingSchemaHandler
{
    public function __construct(
        private GetCatalogQueryInterface $getCatalogQuery,
        private MappingStorageInterface $catalogsMappingStorage,
    ) {
    }

    public function __invoke(UpdateCatalogProductMappingSchemaCommand $command): void
    {
        try {
            $catalog = $this->getCatalogQuery->execute($command->getCatalogId());
        } catch (CatalogNotFoundException) {
            throw new \LogicException('Catalog must exist');
        }

        $this->catalogsMappingStorage->write(
            \sprintf('catalogs/%d/mappings/product.json', $catalog->getId()),
            \json_encode($command->getProductMappingSchema(), JSON_THROW_ON_ERROR),
        );
    }
}

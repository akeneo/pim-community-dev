<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\UpsertCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\ProductMappingSchema\UpdateProductMappingSchemaQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\ServiceAPI\Command\UpdateProductMappingSchemaCommand;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogNotFoundException as ServiceApiCatalogNotFoundException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateProductMappingSchemaHandler
{
    public function __construct(
        readonly private GetCatalogQueryInterface $getCatalogQuery,
        readonly private UpdateProductMappingSchemaQueryInterface $updateProductMappingSchemaQuery,
        readonly private UpsertCatalogQueryInterface $upsertCatalogQuery,
    ) {
    }

    public function __invoke(UpdateProductMappingSchemaCommand $command): void
    {
        try {
            $catalog = $this->getCatalogQuery->execute($command->getCatalogId());
        } catch (CatalogNotFoundException) {
            throw new ServiceApiCatalogNotFoundException();
        }

        $this->updateProductMappingSchemaQuery->execute(
            $catalog->getId(),
            \json_encode($command->getProductMappingSchema(), JSON_THROW_ON_ERROR),
        );

        $this->updateProductMapping($catalog, $command->getProductMappingSchema());
    }

    private function updateProductMapping(Catalog $catalog, object $productMappingSchema): void
    {
        $currentProductMapping = $catalog->getProductMapping();
        $newProductMapping = [];

        /** @var array<string, object> $properties */
        $properties = (array) ((array) $productMappingSchema)['properties'];

        $targetCodes = \array_keys($properties);

        foreach ($targetCodes as $targetCode) {
            if (\array_key_exists($targetCode, $currentProductMapping)) {
                $newProductMapping[$targetCode] = $currentProductMapping[$targetCode];
            } else {
                $newProductMapping[$targetCode] = [
                    'source' => 'uuid' === $targetCode ? $targetCode : null,
                    'scope' => null,
                    'locale' => null,
                ];
            }
        }

        $this->upsertCatalogQuery->execute(new Catalog(
            $catalog->getId(),
            $catalog->getName(),
            $catalog->getOwnerUsername(),
            $catalog->isEnabled(),
            $catalog->getProductSelectionCriteria(),
            $catalog->getProductValueFilters(),
            $newProductMapping,
        ));
    }
}

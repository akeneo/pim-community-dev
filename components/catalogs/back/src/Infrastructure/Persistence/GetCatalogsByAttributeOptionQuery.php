<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\GetCatalogsByAttributeOptionQueryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCatalogsByAttributeOptionQuery implements GetCatalogsByAttributeOptionQueryInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * {@inheritDoc}
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function execute(AttributeOptionInterface $attributeOption): array
    {
        $query = <<<SQL
            SELECT BIN_TO_UUID(catalog.id) AS id
            FROM akeneo_catalog AS catalog,
                 JSON_TABLE(catalog.product_selection_criteria, '$[*]' COLUMNS (
                     field VARCHAR(40)  PATH '$.field',
                     value json PATH '$.value')
                ) AS criterion
            WHERE criterion.field = 'color' AND JSON_CONTAINS(criterion.value, json_quote("blue"), '$')
            AND catalog.is_enabled
        SQL;

        return $this->connection->executeQuery($query, [
            'attributeCode' => $attributeOption->getAttribute()->getCode(),
            'attributeOptionCode' => $attributeOption->getCode(),
        ])->fetchFirstColumn();
    }
}

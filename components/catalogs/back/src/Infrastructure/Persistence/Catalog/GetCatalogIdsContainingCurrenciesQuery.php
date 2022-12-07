<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogIdsContainingCurrenciesQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCatalogIdsContainingCurrenciesQuery implements GetCatalogIdsContainingCurrenciesQueryInterface
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * {@inheritdoc}
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \JsonException
     */
    public function execute(array $currencyCodes): array
    {
        $query = <<<SQL
            SELECT DISTINCT BIN_TO_UUID(id)
            FROM akeneo_catalog,
                 JSON_TABLE(product_selection_criteria, '$[*]' COLUMNS (
                     field VARCHAR(255)  PATH '$.field',
                     value json PATH '$.value')
                     ) AS criterion
            WHERE criterion.field = 'currencies' AND JSON_OVERLAPS(criterion.value, :categoryCodes)
            AND is_enabled IS TRUE
        SQL;

        /** @var string[] $catalogIds */
        $catalogIds = $this->connection->executeQuery($query, [
            'categoryCodes' => \json_encode($currencyCodes, JSON_THROW_ON_ERROR),
        ])->fetchFirstColumn();

        return $catalogIds;
    }
}

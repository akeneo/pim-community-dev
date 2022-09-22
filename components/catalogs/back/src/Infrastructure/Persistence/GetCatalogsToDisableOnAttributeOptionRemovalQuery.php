<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\GetCatalogsToDisableOnAttributeOptionRemovalQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCatalogsToDisableOnAttributeOptionRemovalQuery implements GetCatalogsToDisableOnAttributeOptionRemovalQueryInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function execute(string $attributeCode, string $attributeOptionCode): array
    {
        $query = <<<SQL
            SELECT DISTINCT BIN_TO_UUID(id)
            FROM akeneo_catalog,
                 JSON_TABLE(product_selection_criteria, '$[*]' COLUMNS (
                     field VARCHAR(255)  PATH '$.field',
                     value json PATH '$.value')
                ) AS criterion
            WHERE criterion.field = :attributeCode AND JSON_CONTAINS(criterion.value, json_quote(:attributeOptionCode), '$')
            AND is_enabled IS TRUE
        SQL;

        /** @var array<string> $ids */
        $ids = $this->connection->executeQuery($query, [
            'attributeCode' => $attributeCode,
            'attributeOptionCode' => $attributeOptionCode,
        ])->fetchFirstColumn();

        return $ids;
    }
}

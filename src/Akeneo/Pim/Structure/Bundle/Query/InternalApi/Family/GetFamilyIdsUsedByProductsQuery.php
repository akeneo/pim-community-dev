<?php
declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\InternalApi\Family;

use Akeneo\Pim\Structure\Component\Query\InternalApi\GetFamilyIdsUsedByProductsQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetFamilyIdsUsedByProductsQuery implements GetFamilyIdsUsedByProductsQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(): array
    {
        $query = <<<SQL
        SELECT family.id
        FROM pim_catalog_family family
        WHERE EXISTS(
            SELECT product.family_id 
            FROM pim_catalog_product product 
            WHERE product.family_id = family.id
        );
        SQL;

        return array_map(function (string $code) {
            return intval($code);
        }, $this->connection->executeQuery($query, [])->fetchFirstColumn());
    }
}

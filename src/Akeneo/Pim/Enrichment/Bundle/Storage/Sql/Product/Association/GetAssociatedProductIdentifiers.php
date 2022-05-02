<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAssociatedProductIdentifiers
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function execute(
        ProductInterface $owner,
        AssociationTypeInterface $associationType
    ): array {
        $query = <<<SQL
SELECT product.identifier
FROM pim_catalog_product product
JOIN pim_catalog_association_product association_product on product.id = association_product.product_id
JOIN pim_catalog_association association on association_product.association_id = association.id
JOIN pim_catalog_association_type association_type on association_type.id = association.association_type_id
WHERE owner_id = :owner
AND association_type.code = :type
SQL;

        return $this->connection->fetchFirstColumn($query, [
            'owner' => $owner->getId(),
            'type' => $associationType->getCode(),
        ]);
    }
}

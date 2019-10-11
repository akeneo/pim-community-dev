<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Family;

use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountProductsWithFamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Doctrine\DBAL\Connection;

/**
 * Count the number of products belonging to the given family
 *
 * @author    Julian Prud'homme <julian.prudhomme@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CountProductsWithFamily implements CountProductsWithFamilyInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function count(FamilyInterface $family): int
    {
        return (int) $this->connection->executeQuery(
            'SELECT COUNT(id) FROM pim_catalog_product WHERE family_id = :family_id',
            ['family_id' => $family->getId()]
        )->fetchColumn();
    }
}

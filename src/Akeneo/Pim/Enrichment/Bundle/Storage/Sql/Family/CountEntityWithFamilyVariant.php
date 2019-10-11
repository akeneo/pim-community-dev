<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Family;

use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountEntityWithFamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Doctrine\DBAL\Connection;

/**
 * Find the number of product and product models count belonging to the given family variant
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CountEntityWithFamilyVariant implements CountEntityWithFamilyVariantInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function belongingToFamilyVariant(FamilyVariantInterface $familyVariant): int
    {
        $productModelCount = $this->countProductModels($familyVariant);
        $productCount = $this->countVariantProducts($familyVariant);

        return $productModelCount + $productCount;
    }

    /**
     * @param FamilyVariantInterface $familyVariant
     *
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    private function countProductModels(FamilyVariantInterface $familyVariant): int
    {
        return (int) $this->connection->executeQuery(
            'SELECT COUNT(id) FROM pim_catalog_product_model WHERE family_variant_id = :family_variant_id',
            ['family_variant_id' => $familyVariant->getId()]
        )->fetchColumn();
    }

    /**
     * @param FamilyVariantInterface $familyVariant
     *
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    private function countVariantProducts(FamilyVariantInterface $familyVariant): int
    {
        return (int) $this->connection->executeQuery(
            'SELECT COUNT(id) FROM pim_catalog_product WHERE family_variant_id = :family_variant_id',
            ['family_variant_id' => $familyVariant->getId()]
        )->fetchColumn();
    }
}

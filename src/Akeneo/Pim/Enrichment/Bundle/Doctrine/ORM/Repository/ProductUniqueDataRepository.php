<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductUniqueDataRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Product unique data repository. Please see {@see Akeneo\Pim\Enrichment\Component\Product\Model\ProductUniqueDataInterface}
 * for more information.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductUniqueDataRepository extends EntityRepository implements ProductUniqueDataRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function uniqueDataExistsInAnotherProduct(ValueInterface $value, ProductInterface $product): bool
    {
        $sql = <<<SQL
        SELECT count(ud.id) as count
        FROM pim_catalog_product_unique_data ud
            JOIN pim_catalog_attribute a ON a.id = ud.attribute_id
        WHERE a.code = :attribute_code AND ud.raw_data = :data AND ud.product_uuid != :product_uuid
        GROUP BY ud.id
        SQL;

        $count = (int) $this->getEntityManager()->getConnection()->executeQuery($sql, [
            'attribute_code' => $value->getAttributeCode(),
            'data' => $value->__toString(),
            'product_uuid' => $product->getUuid()->getBytes(),
        ])->fetchOne();

        return 0 !== $count;
    }
}

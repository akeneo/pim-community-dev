<?php
namespace Oro\Bundle\ProductBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Custom repository for product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ProductEntityRepository extends EntityRepository
{

    /**
     * Find all products and return as results
     *
     * @param array      $attributeCodes
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array The objects.
     */
    public function findByAttributes(array $attributeCodes, array $criteria = null, array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->_em->createQueryBuilder();

        // TODO : load only selected attribute values (have to refactor AbstractOrmEntity::__call and __get
        // too to avoid lazy loading on others attribtes)
        // TODO refactor in basic datamodel repository
        $qb
            ->select('Product', 'Value')
            ->from('Oro\Bundle\ProductBundle\Entity\Product', 'Product')
            ->leftJoin('Product.values', 'Value')
            ->innerJoin('Value.attribute', 'Attribute');

        return $qb->getQuery()->getResult();
    }
}

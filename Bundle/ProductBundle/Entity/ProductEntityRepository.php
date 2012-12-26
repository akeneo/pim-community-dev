<?php
namespace Oro\Bundle\ProductBundle\Entity;

use Oro\Bundle\DataModelBundle\Entity\OrmEntityRepository;

/**
 * Custom repository for product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ProductEntityRepository extends OrmEntityRepository
{

    /**
     * Find all products and return as results
     *
     * @param array      $attributeCodes attributes codes
     * @param array      $criteria       criteria
     * @param array|null $orderBy        order
     * @param int|null   $limit          limit
     * @param int|null   $offset         offset
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
            ->select('Product', 'Value', 'Attribute')
            ->from('Oro\Bundle\ProductBundle\Entity\Product', 'Product')
            ->leftJoin('Product.values', 'Value')
            ->innerJoin('Value.attribute', 'Attribute');
        // inner failed if 0 values

        return $qb->getQuery()->getResult();
    }
}

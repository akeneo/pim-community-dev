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
        // TODO : we should customize findBy or create a custom orm/doctrine/persister see BasicEntityPersister::loadAll

        // get base fields
        $qb = $this->createQueryBuilder('p')
            ->addSelect('p.id, p.sku, p.created, p.updated');
        // get attributes and backend type
        if (count($attributeCodes)) {
            $qbAtt = $this->_em->createQueryBuilder()
                ->select('att.code,att.id,att.type')
                ->from('Oro\Bundle\ProductBundle\Entity\ProductAttribute', 'att')
                ->where('att.code IN (\''.implode('\',\'', $attributeCodes).'\')');
            $attributes = $qbAtt->getQuery()->getArrayResult();
            // get any attributes values
            foreach ($attributes as $attribute) {
                $tableAlias = 'v'.$attribute['code'];
                $backendField = $attribute['type'].'Value';
                $qb
                    ->addSelect($tableAlias.'.'.$backendField.' as '.$attribute['code'])
                    ->leftJoin('p.values', $tableAlias, \Doctrine\ORM\Query\Expr\Join::WITH, $tableAlias.'.attribute = '.$attribute['id']);
            }
        }

        return $qb->getQuery()->getResult();
    }
}

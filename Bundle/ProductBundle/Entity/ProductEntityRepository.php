<?php
namespace Oro\Bundle\ProductBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Custom repository for product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductEntityRepository extends EntityRepository
{

    /**
     * Find all products and return as results
     *
     * @param array $attributeCodes
     * @return array
     */
    public function findAllAsResults($attributeCodes)
    {
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
        return $qb->getQuery()->getArrayResult();
    }
}

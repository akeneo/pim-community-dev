<?php
namespace Pim\Bundle\ProductBundle\Entity\Repository;

use Pim\Bundle\ProductBundle\Entity\ProductSegment;

use Oro\Bundle\SegmentationTreeBundle\Entity\Repository\SegmentRepository;

/**
 * ProductSegment repository
 * Override SegmentRepository of OroSegmentationTreeBundle
 *     Allow to count products linked to nodes
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductSegmentRepository extends SegmentRepository
{

    /**
     * Shortcut to get all children query builder
     *
     * @param ProductSegment $segment
     * @param boolean        $includeNode
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getAllChildrenQueryBuilder(ProductSegment $segment, $includeNode = false)
    {
        return $this->getChildrenQueryBuilder($segment, false, null, 'ASC', $includeNode);
    }

    /**
     * Count products linked to a node.
     * You can define if you just want to get the property of the actual node
     * or with its children with the direct parameter
     * The third parameter allow to include the actual node or not
     *
     * @param ProductSegment $segment     the requested node
     * @param boolean        $onlyActual  true to take only actual node
     * @param boolean        $includeNode true to include actual node in query result
     *
     * @return integer
     */
    public function countProductsLinked(ProductSegment $segment, $onlyActual = true, $includeNode = true)
    {
        $qb = ($onlyActual) ?
            $this->getNodeQueryBuilder($segment) :
            $this->getAllChildrenQueryBuilder($segment, $includeNode);

        $rootAlias = $qb->getRootAliases();
        $firstRootAlias = $rootAlias[0];

        $qb->select($qb->expr()->count('p'))
           ->join($firstRootAlias .'.products', 'p');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Create a query builder with just a link to the product segment passed in parameter
     *
     * @param ProductSegment $segment
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getNodeQueryBuilder(ProductSegment $segment)
    {
        $qb = $this->createQueryBuilder('ps');
        $qb->where('ps.id = :nodeId')
           ->setParameter('nodeId', $segment->getId());

        return $qb;
    }
}

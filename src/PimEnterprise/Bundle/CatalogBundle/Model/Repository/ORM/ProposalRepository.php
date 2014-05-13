<?php

namespace PimEnterprise\Bundle\CatalogBundle\Model\Repository\ORM;

use Doctrine\ORM\EntityRepository;

/**
 * Proposal ORM repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProposalRepository extends EntityRepository
{
    /**
     * @return QueryBuilder
     */
    public function createDatagridQueryBuilder()
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p.id')
            ->addSelect('p.createdBy')
            ->addSelect('p.createdAt')
            ->addSelect('p.changes')
            ->where('p.product = :product');
    }
}

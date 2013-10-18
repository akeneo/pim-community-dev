<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Doctrine\EntityRepository;

/**
 * Repository
 *
 * @author    Nicolas <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeRepository extends EntityRepository
{
    /**
     * @param string $className
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function buildAllByEntity($className)
    {
        return $this->build()->where('group_type.entity = :entity')
            ->setParameter('entity', $className)
            ->addOrderBy('group_type.code', 'ASC');
    }
}

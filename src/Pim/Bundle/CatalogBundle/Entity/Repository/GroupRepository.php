<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Doctrine\EntityRepository;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Doctrine\ORM\AbstractQuery;

/**
 * Repository
 *
 * @author    Nicolas <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupRepository extends EntityRepository
{
    /**
     * Get ordered groups associative array id to label
     *
     * @return array
     */
    public function getChoicesByType(GroupType $type)
    {
        $alias = $this->getAlias();
        $qb = $this->build()
            ->where($alias.'.type = :groupType')
            ->addOrderBy($alias.'.code', 'ASC')
            ->setParameter('groupType', $type);
        $groups = $qb->getQuery()->getResult();
        $choices = array();
        foreach ($groups as $group) {
            $choices[$group->getId()]= $group->getCode();
        }

        return $choices;
    }

    /**
     * @return string
     */
    protected function getAlias()
    {
        return 'ProductGroup';
    }

}

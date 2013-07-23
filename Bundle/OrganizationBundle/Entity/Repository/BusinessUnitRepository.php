<?php
namespace Oro\Bundle\OrganizationBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;


class BusinessUnitRepository extends EntityRepository
{
     /**
     * @return array
     */
    public function getBusinessUnitsTree()
    {
        $businessUnits = $this->createQueryBuilder('businessUnit')
                    ->select(array('businessUnit.id','businessUnit.name','IDENTITY(businessUnit.parent) parent'))
                    ->getQuery()
                    ->getArrayResult();
        $children = array();
        foreach ($businessUnits as &$businessUnit) {
            $parent = $businessUnit['parent'] ?: 0;
            $children[$parent][] = &$businessUnit;
        }
        unset($businessUnit);
        foreach($businessUnits as &$businessUnit) {
            if (isset($children[$businessUnit['id']])) {
                $businessUnit['children'] = $children[$businessUnit['id']];
            }
        }
        unset($businessUnit);

        return $children[0];
    }
}

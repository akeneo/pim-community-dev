<?php
namespace Oro\Bundle\OrganizationBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\UserBundle\Entity\User;

class BusinessUnitRepository extends EntityRepository
{
     /**
     * @return array
     */
    public function getBusinessUnitsTree(User $user)
    {
        $businessUnits = $this->createQueryBuilder('businessUnit')
                    ->select(
                        array(
                            'businessUnit.id',
                            'businessUnit.name',
                            'IDENTITY(businessUnit.parent) parent',
                            'CASE WHEN users.id <> 0 THEN 1 ELSE 0 END as hasUser'
                        )
                    );

        if ($user->getId()) {
            $businessUnits->leftJoin('businessUnit.users', 'users', Expr\Join::WITH, 'users.id = :user')
                ->setParameter('user', $user);
        }
        //var_dump($businessUnits->getQuery()->getSQL());die();
        $businessUnits = $businessUnits->getQuery()->getArrayResult();
        //var_dump($businessUnits);die();
        $children = array();
        foreach ($businessUnits as &$businessUnit) {
            $parent = $businessUnit['parent'] ?: 0;
            $children[$parent][] = &$businessUnit;
        }
        unset($businessUnit);
        foreach ($businessUnits as &$businessUnit) {
            if (isset($children[$businessUnit['id']])) {
                $businessUnit['children'] = $children[$businessUnit['id']];
            }
        }
        unset($businessUnit);

        if (isset($children[0])) {
            $children = $children[0];
        }

        return $children;
    }

    /**
     * @param array $businessUnits
     */
    public function getBusinessUnits(array $businessUnits)
    {
        return $this->createQueryBuilder('businessUnit')
            ->select('businessUnit')
            ->where('businessUnit.id in (:ids)')
            ->setParameter('ids', $businessUnits)
            ->getQuery()
            ->execute();
    }
}

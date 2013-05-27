<?php
namespace Oro\Bundle\UserBundle\Entity\Repository;

use Doctrine\ORM\Query\Expr;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\Acl;

class AclRepository extends NestedTreeRepository
{

    /**
     * Get array with allowed acl resources for role array
     *
     * @param  Role[] $roles
     * @param  bool $useObjects
     * @return Acl[]|array
     */
    public function getAllowedAclResourcesForRoles(array $roles, $useObjects = false)
    {
        $allowedAcl = array();
        $qb = $this->createQueryBuilder('acl');

        if ($useObjects) {
            $qb->select('acl');
        } else {
            $qb->select('acl.id');
        }

        $qb->where('acl.rgt > :left_key')
            ->andWhere('acl.lft < :right_key')
            ->andWhere('acl.id != :rootNode')
            ->setParameter('rootNode', Acl::ROOT_NODE)
            ->orderBy('acl.lft');

        foreach ($roles as $role) {
            $aclList = $role->getAclResources();
            if (count($aclList)) {
                foreach ($aclList as $acl) {
                    $rootAcl = false;
                    if ($acl->getId() == Acl::ROOT_NODE) {
                        $rootAcl = $acl;
                    }
                    $query = $qb->setParameter('left_key', $acl->getLft())
                        ->setParameter('right_key', $acl->getRgt())
                        ->getQuery();

                    if ($useObjects) {
                        $acls = $query->getResult();

                    } else {
                        $aclList = $query->getScalarResult();
                        $acls = array();
                        foreach ($aclList as $scalar) {
                            $acls[] = $scalar['id'];
                        }
                    }

                    $allowedAcl = $this->arrayUnique(
                        array_merge(
                            $allowedAcl,
                            $acls
                        ),
                        $useObjects
                    );

                    if ($rootAcl) {
                        $root = $useObjects ? $rootAcl : $rootAcl->getId();
                        $allowedAcl[] = $root;
                    }
                }
            }
        }

        return $allowedAcl;
    }

    private function arrayUnique($array, $isObjects = true)
    {
        if (!$isObjects) {
            return array_unique($array);
        } else {
            $final = array();
            foreach ($array as $object) {
                if (!in_array($object, $final)) {
                    $final[] = $object;
                }
            }

            return $final;
        }
    }

    /**
     * Get full node list with roles for acl resource
     *
     * @param  Acl $acl
     * @return Acl[]
     */
    public function getFullNodeWithRoles(Acl $acl)
    {
        return $this->createQueryBuilder('acl')
            ->select(array('acl', 'role'))
            ->leftJoin('acl.accessRoles', 'role')
            ->where('acl.rgt > :left_key')
            ->andWhere('acl.lft < :right_key')
            ->orderBy('acl.lft')
            ->setParameter('left_key', $acl->getLft())
            ->setParameter('right_key', $acl->getRgt())
            ->getQuery()
            ->getResult();
    }

    /**
     * @param  string $aclId
     * @return array
     */
    public function getAclRolesWithoutTree($aclId)
    {
        $aclRoles = array();
        $roles = $this->getEntityManager()->createQueryBuilder('acl')
            ->select('role.role')
            ->from('OroUserBundle:role', 'role')
            ->join('role.aclResources', 'acl')
            ->where('acl.id = :aclId')
            ->setParameter('aclId', $aclId)
            ->getQuery()
            ->getScalarResult();
        foreach ($roles as $role) {
            $aclRoles[] = $role['role'];
        }

        return $aclRoles;
    }

    /**
     * Get Acl array for role
     *
     * @param  Role $role
     * @return array
     */
    public function getAclListWithRoles(Role $role)
    {
         $query = $this->createQueryBuilder('acl')
            ->select('acl', 'accessRoles')
            ->orderBy('acl.root, acl.lft', 'ASC');

        if ($role->getId()) {
             $query->leftJoin('acl.accessRoles', 'accessRoles', Expr\Join::WITH, 'accessRoles.id = :role')
                 ->setParameter('role', $role);
        } else {
            $query->leftJoin('acl.accessRoles', 'accessRoles');
        }

         return $query->getQuery()
            ->getArrayResult();
    }

    /**
     * Get Acl tree for role
     *
     * @param  Role $role
     * @return array
     */
    public function getRoleAclTree(Role $role)
    {
        return $this->buildTree($this->getAclListWithRoles($role));
    }

    /**
     * Get ACL Resources ids list
     *
     * @return array
     */
    public function getAclResourcesIds()
    {
        $acl = array();

        $aclArray =  $this->createQueryBuilder('acl')
            ->select('acl.id')
            ->getQuery()
            ->getArrayResult();

        foreach ($aclArray as $aclResult) {
            $acl[] = $aclResult['id'];
        }

        return $acl;
    }
}

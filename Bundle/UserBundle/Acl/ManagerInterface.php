<?php

namespace Oro\Bundle\UserBundle\Acl;

use Oro\Bundle\UserBundle\Entity\User;

interface ManagerInterface
{
    /**
     * Check permissions for resource for user.
     *
     * @param  string                             $aclResourceId
     * @param  \Oro\Bundle\UserBundle\Entity\User $user
     * @return bool
     */
    public function isResourceGranted($aclResourceId, User $user = null);

    /**
     * Check permissions for class method
     *
     * @param  string                             $class
     * @param  string                             $method
     * @param  \Oro\Bundle\UserBundle\Entity\User $user
     * @return bool
     */
    public function isClassMethodGranted($class, $method, User $user = null);

    /**
     * Add/Remove Acl resource for Role
     *
     * @param int    $roleId Role id
     * @param string $aclId  ACL Resource ID
     * @param bool   $isAdd  true if add, false if delete
     */
    public function modifyAclForRole($roleId, $aclId, $isAdd = true);

    /**
     * Add/Remove Acl resources for Role
     *
     * @param int   $roleId Role id
     * @param array $aclIds ACL Resource IDs
     * @param bool  $isAdd  true if add, false if delete
     */
    public function modifyAclsForRole($roleId, array $aclIds, $isAdd = true);

    /**
     * Search Acl resource by id
     *
     * @param  string                            $aclId ACL Resource ID
     * @return \Oro\Bundle\UserBundle\Entity\Acl
     */
    public function getAclResource($aclId);

    /**
     * Get ACL Resources list
     *
     * @param  bool                                      $useObjects Use objects or plain ids in response
     * @return \Oro\Bundle\UserBundle\Entity\Acl[]|array
     */
    public function getAclResources($useObjects = true);

    /**
     * Get list of allowed ACL resources for roles array
     *
     * @param  \Oro\Bundle\UserBundle\Entity\Role[]      $roles
     * @param  bool                                      $useObjects true will return array of Acl objects, false - array ot ids
     * @return array|\Oro\Bundle\UserBundle\Entity\Acl[]
     */
    public function getAllowedAclResourcesForRoles(array $roles, $useObjects = false);
}

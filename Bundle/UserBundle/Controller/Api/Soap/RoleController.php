<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Soap;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\SoapBundle\Controller\Api\Soap\SoapController;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;

class RoleController extends SoapController
{
    /**
     * @Soap\Method("getRoles")
     * @Soap\Param("page", phpType="int")
     * @Soap\Param("limit", phpType="int")
     * @Soap\Result(phpType="Oro\Bundle\UserBundle\Entity\Role[]")
     * @AclAncestor("oro_user_role_list")
     */
    public function cgetAction($page = 1, $limit = 10)
    {
        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * @Soap\Method("getRole")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="Oro\Bundle\UserBundle\Entity\Role")
     * @AclAncestor("oro_user_role_show")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * @Soap\Method("createRole")
     * @Soap\Param("role", phpType="Oro\Bundle\UserBundle\Entity\Role")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("oro_user_role_create")
     */
    public function createAction($role)
    {
        return $this->handleCreateRequest();
    }

    /**
     * @Soap\Method("updateRole")
     * @Soap\Param("id", phpType="int")
     * @Soap\Param("role", phpType="Oro\Bundle\UserBundle\Entity\Role")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("oro_user_role_update")
     */
    public function updateAction($id, $role)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * @Soap\Method("deleteRole")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("oro_user_role_remove")
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * @Soap\Method("getRoleByName")
     * @Soap\Param("name", phpType="string")
     * @Soap\Result(phpType="Oro\Bundle\UserBundle\Entity\Role")
     * @AclAncestor("oro_user_role_show")
     */
    public function getBynameAction($name)
    {
        $entity = $this->getManager()->getRepository()->findOneBy(array('role' => $name));

        if (!$entity) {
            throw new \SoapFault('NOT_FOUND', sprintf('Role "%s" can not be found', $name));
        }

        return $entity;
    }

    /**
     * @Soap\Method("getRoleAcl")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "string[]")
     * @AclAncestor("oro_user_acl_edit")
     */
    public function getAclAction($id)
    {
        $role = $this->getEntity($id);
        if (!$role) {
            throw new \SoapFault('NOT_FOUND', sprintf('Role with id "%s" can not be found', $id));
        }

        return $this->getAclManager()->getAllowedAclResourcesForRoles(array($role));
    }

    /**
     * Link ACL resource to role
     *
     * @param  int    $id       Role id
     * @param  string $resource ACL resource id
     * @return string
     *
     * @Soap\Method("addAclToRole")
     * @Soap\Param("id", phpType="int")
     * @Soap\Param("resource", phpType="string")
     * @Soap\Result(phpType="string")
     * @AclAncestor("oro_user_role_acl")
     */
    public function postAclAction($id, $resource)
    {
        $this->getAclManager()->modifyAclForRole($id, $resource, true);

        return '';
    }

    /**
     * Unlink ACL resource from role
     *
     * @param  int    $id       Role id
     * @param  string $resource ACL resource id
     * @return string
     *
     * @Soap\Method("removeAclFromRole")
     * @Soap\Param("id", phpType="int")
     * @Soap\Param("resource", phpType="string")
     * @Soap\Result(phpType="string")
     * @AclAncestor("oro_user_role_acl")
     */
    public function deleteAclAction($id, $resource)
    {
        $this->getAclManager()->modifyAclForRole($id, $resource, false);

        return '';
    }

    /**
     * Link ACL resources to role
     *
     * @param  int    $id        Role id
     * @param  array  $resources Array of ACL resource ids
     * @return string
     *
     * @Soap\Method("addAclsToRole")
     * @Soap\Param("id", phpType="int")
     * @Soap\Param("resources", phpType="string[]")
     * @Soap\Result(phpType="string")
     * @AclAncestor("oro_user_role_acl")
     */
    public function addAclsToRoleAction($id, $resources)
    {
        $this->getAclManager()->modifyAclsForRole($id, $resources, true);

        return '';
    }

    /**
     * Unlink ACL resources from role
     *
     * @param  int    $id        Role id
     * @param  array  $resources Array of ACL resource ids
     * @return string
     *
     * @Soap\Method("removeAclsFromRole")
     * @Soap\Param("id", phpType="int")
     * @Soap\Param("resources", phpType="string[]")
     * @Soap\Result(phpType="string")
     * @AclAncestor("oro_user_role_acl")
     */
    public function deleteAclsAction($id, $resources)
    {
        $this->getAclManager()->modifyAclForRole($id, $resources, false);

        return '';
    }

    /**
     * @return \Oro\Bundle\UserBundle\Acl\Manager
     */
    protected function getAclManager()
    {
        return $this->container->get('oro_user.acl_manager');
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->container->get('oro_user.role_manager.api');
    }

    /**
     * @inheritdoc
     */
    public function getForm()
    {
        return $this->container->get('oro_user.form.role.api');
    }

    /**
     * @inheritdoc
     */
    public function getFormHandler()
    {
        return $this->container->get('oro_user.form.handler.role.api');
    }
}

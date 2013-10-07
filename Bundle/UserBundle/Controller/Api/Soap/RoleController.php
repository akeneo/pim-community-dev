<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Soap;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\SoapBundle\Controller\Api\Soap\SoapController;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class RoleController extends SoapController
{
    /**
     * @Soap\Method("getRoles")
     * @Soap\Param("page", phpType="int")
     * @Soap\Param("limit", phpType="int")
     * @Soap\Result(phpType="Oro\Bundle\UserBundle\Entity\Role[]")
     * @AclAncestor("oro_user_role_view")
     */
    public function cgetAction($page = 1, $limit = 10)
    {
        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * @Soap\Method("getRole")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="Oro\Bundle\UserBundle\Entity\Role")
     * @AclAncestor("oro_user_role_view")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * @Soap\Method("createRole")
     * @Soap\Param("role", phpType="Oro\Bundle\UserBundle\Entity\Role")
     * @Soap\Result(phpType="int")
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
     * @AclAncestor("oro_user_role_view")
     */
    public function getBynameAction($name)
    {
        $entity = $this->getManager()->getRepository()->findOneBy(array('label' => $name));

        if (!$entity) {
            throw new \SoapFault('NOT_FOUND', sprintf('Role "%s" can not be found', $name));
        }

        return $entity;
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

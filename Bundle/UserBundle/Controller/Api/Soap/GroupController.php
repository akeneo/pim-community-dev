<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Soap;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use Oro\Bundle\SoapBundle\Controller\Api\Soap\SoapController;
use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class GroupController extends SoapController
{
    /**
     * @Soap\Method("getGroups")
     * @Soap\Param("page", phpType="int")
     * @Soap\Param("limit", phpType="int")
     * @Soap\Result(phpType="Oro\Bundle\UserBundle\Entity\Group[]")
     * @AclAncestor("oro_user_group_view")
     */
    public function cgetAction($page = 1, $limit = 10)
    {
        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * @Soap\Method("getGroup")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="Oro\Bundle\UserBundle\Entity\Group")
     * @AclAncestor("oro_user_group_view")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * @Soap\Method("createGroup")
     * @Soap\Param("group", phpType="\Oro\Bundle\UserBundle\Entity\Group")
     * @Soap\Result(phpType="int")
     * @AclAncestor("oro_user_group_create")
     */
    public function createAction($group)
    {
        return $this->handleCreateRequest();
    }

    /**
     * @Soap\Method("updateGroup")
     * @Soap\Param("id", phpType="int")
     * @Soap\Param("group", phpType="\Oro\Bundle\UserBundle\Entity\Group")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("oro_user_group_update")
     */
    public function updateAction($id, $group)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * @Soap\Method("deleteGroup")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("oro_user_group_remove")
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * @Soap\Method("getGroupRoles")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="Oro\Bundle\UserBundle\Entity\Role[]")
     * @AclAncestor("oro_user_role_view")
     */
    public function getRolesAction($id)
    {
        $entity = $this->getEntity($id);

        return $entity->getRoles()->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->container->get('oro_user.group_manager.api');

    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->container->get('oro_user.form.group.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->container->get('oro_user.form.handler.group.api');
    }
}

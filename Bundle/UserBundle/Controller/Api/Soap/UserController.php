<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Soap;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use Oro\Bundle\SoapBundle\Controller\Api\Soap\FlexibleSoapController;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;

class UserController extends FlexibleSoapController
{
    /**
     * @Soap\Method("getUsers")
     * @Soap\Param("page", phpType="int")
     * @Soap\Param("limit", phpType="int")
     * @Soap\Result(phpType="Oro\Bundle\UserBundle\Entity\User[]")
     * @AclAncestor("oro_user_user_list")
     */
    public function cgetAction($page = 1, $limit = 10)
    {
        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * @Soap\Method("getUser")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="Oro\Bundle\UserBundle\Entity\User")
     * @AclAncestor("oro_user_user_view")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * @Soap\Method("createUser")
     * @Soap\Param("user", phpType="Oro\Bundle\UserBundle\Entity\UserSoap")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("oro_user_user_create")
     */
    public function createAction($user)
    {
        return $this->handleCreateRequest();
    }

    /**
     * @Soap\Method("updateUser")
     * @Soap\Param("id", phpType="int")
     * @Soap\Param("user", phpType="Oro\Bundle\UserBundle\Entity\UserSoap")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("oro_user_user_update")
     */
    public function updateAction($id, $user)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * @Soap\Method("deleteUser")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("oro_user_user_delete")
     */
    public function deleteAction($id)
    {
        $securityToken = $this->container->get('security.context')->getToken();
        $user = $securityToken ? $securityToken->getUser() : null;
        if (is_object($user) && $user->getId() != $id) {
            return $this->handleDeleteRequest($id);
        } else {
            throw new \SoapFault('BAD_REQUEST', 'Self delete forbidden');
        }
    }

    /**
     * @Soap\Method("getUserRoles")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="Oro\Bundle\UserBundle\Entity\Role[]")
     * @AclAncestor("oro_user_user_roles")
     */
    public function getRolesAction($id)
    {
        return $this->getEntity($id)->getRoles();
    }

    /**
     * @Soap\Method("getUserGroups")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="Oro\Bundle\UserBundle\Entity\Group[]")
     * @AclAncestor("oro_user_user_groups")
     */
    public function getGroupsAction($id)
    {
        return $this->getEntity($id)->getGroups();
    }

    /**
     * @Soap\Method("getUserAcl")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="string[]")
     * @AclAncestor("oro_user_user_acl")
     */
    public function getAclAction($id)
    {
        return $this->getAclManager()->getAclForUser(
            $this->getEntity($id)
        );
    }

    /**
     * @Soap\Method("getUserBy")
     * @Soap\Param("filters", phpType="BeSimple\SoapCommon\Type\KeyValue\String[]")
     * @Soap\Result(phpType="Oro\Bundle\UserBundle\Entity\User")
     * @AclAncestor("oro_user_user_view")
     */
    public function getByAction(array $filters)
    {
        if (empty($filters)) {
            throw new \SoapFault('NOT_FOUND', 'Empty filter data');
        }

        $entity = $this->getUserManager()->findUserBy($filters);

        if (!$entity) {
            throw new \SoapFault('NOT_FOUND', 'User cannot be found using specified filter');
        }

        return $entity;
    }

    /**
     * @return \Oro\Bundle\UserBundle\Acl\Manager
     */
    protected function getAclManager()
    {
        return $this->container->get('oro_user.acl_manager');
    }

    /**
     * @return \Oro\Bundle\UserBundle\Entity\UserManager
     */
    protected function getUserManager()
    {
        return $this->container->get('oro_user.manager');
    }

    /**
     * @return \Oro\Bundle\SoapBundle\Entity\Manager\ApiFlexibleEntityManager
     */
    public function getManager()
    {
        return $this->container->get('oro_user.manager.api');
    }

    /**
     * @inheritdoc
     */
    public function getForm()
    {
        return $this->container->get('oro_user.form.user.api');
    }

    /**
     * @inheritdoc
     */
    public function getFormHandler()
    {
        return $this->container->get('oro_user.form.handler.user.api');
    }
}

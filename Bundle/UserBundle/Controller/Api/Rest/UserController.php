<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\Form\FormInterface;

use FOS\Rest\Util\Codes;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiFlexibleEntityManager;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\FlexibleRestController;

/**
 * @NamePrefix("oro_api_")
 */
class UserController extends FlexibleRestController implements ClassResourceInterface
{
    /**
     * Get the list of users
     *
     * @QueryParam(name="page", requirements="\d+", nullable=true, description="Page number, starting from 1. Defaults to 1.")
     * @QueryParam(name="limit", requirements="\d+", nullable=true, description="Number of items per page. defaults to 10.")
     * @return \Symfony\Component\HttpFoundation\Response
     * @ApiDoc(
     *      description="Get the list of users",
     *      resource=true,
     *      filters={
     *          {"name"="page", "dataType"="integer"},
     *          {"name"="limit", "dataType"="integer"}
     *      }
     * )
     * @AclAncestor("oro_user_user_view")
     */
    public function cgetAction()
    {
        $page = (int) $this->getRequest()->get('page', 1);
        $limit = (int) $this->getRequest()->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * Get user data
     *
     * @param int $id User id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @ApiDoc(
     *      description="Get user data",
     *      resource=true,
     *      requirements={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @AclAncestor("oro_user_user_view")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * Create new user
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @ApiDoc(
     *      description="Create new user",
     *      resource=true
     * )
     * @AclAncestor("oro_user_user_create")
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * Update existing user
     *
     * @param int $id User id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @ApiDoc(
     *      description="Update existing user",
     *      resource=true,
     *      requirements={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @AclAncestor("oro_user_user_update")
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Delete user
     *
     * @param int $id User id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @ApiDoc(
     *      description="Delete user",
     *      resource=true,
     *      requirements={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @Acl(
     *      id="oro_user_user_delete",
     *      type="entity",
     *      class="OroUserBundle:User",
     *      permission="DELETE"
     * )
     */
    public function deleteAction($id)
    {
        $securityToken = $this->get('security.context')->getToken();
        $user = $securityToken ? $securityToken->getUser() : null;
        if (is_object($user) && $user->getId() != $id) {
            return $this->handleDeleteRequest($id);
        } else {
            return $this->handleView($this->view(null, Codes::HTTP_FORBIDDEN));
        }
    }

    /**
     * Get user roles
     *
     * @param int $id User id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @ApiDoc(
     *      description="Get user roles",
     *      resource=true,
     *      requirements={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @AclAncestor("oro_user_role_view")
     */
    public function getRolesAction($id)
    {
        $entity = $this->getManager()->getRepository()->findOneBy(array('id' => (int) $id));

        if (!$entity) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        return $this->handleView($this->view($entity->getRoles(), Codes::HTTP_OK));
    }

    /**
     * Get user groups
     *
     * @param int $id User id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @ApiDoc(
     *      description="Get user groups",
     *      resource=true,
     *      requirements={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @AclAncestor("oro_user_group_view")
     */
    public function getGroupsAction($id)
    {
        $entity = $this->getManager()->find($id);

        if (!$entity) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        return $this->handleView($this->view($entity->getGroups(), Codes::HTTP_OK));
    }

    /**
     * Filter user by username or email
     *
     * @QueryParam(name="email", requirements="[a-zA-Z0-9\-_\.@]+", nullable=true, description="Email to filter")
     * @QueryParam(name="username", requirements="[a-zA-Z0-9\-_\.]+", nullable=true, description="Username to filter")
     * @return \Symfony\Component\HttpFoundation\Response
     * @ApiDoc(
     *      description="Get user by username or email",
     *      resource=true,
     *      filters={
     *          {"name"="email", "dataType"="string"},
     *          {"name"="username", "dataType"="string"}
     *      }
     * )
     * @AclAncestor("oro_user_user_view")
     */
    public function getFilterAction()
    {
        $params = $this->getRequest()->query->all();

        if (empty($params)) {
            return $this->handleView($this->view('', Codes::HTTP_BAD_REQUEST));
        }

        $entity = $this->getManager()->getRepository()->findOneBy($params);

        return $this->handleView(
            $this->view(
                $entity,
                $entity ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND
            )
        );
    }

    /**
     * Get entity Manager
     *
     * @return ApiFlexibleEntityManager
     */
    public function getManager()
    {
        return $this->get('oro_user.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->get('oro_user.form.user.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('oro_user.form.handler.user.api');
    }
}

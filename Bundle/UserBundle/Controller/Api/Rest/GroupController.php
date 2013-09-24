<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Rest;

use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\NamePrefix;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;

/**
 * @NamePrefix("oro_api_")
 */
class GroupController extends RestController implements ClassResourceInterface
{
    /**
     * Get the list of groups
     *
     * @ApiDoc(
     *      description="Get the list of groups",
     *      resource=true
     * )
     * @QueryParam(name="page", requirements="\d+", nullable=true, description="Page number, starting from 1. Defaults to 1.")
     * @QueryParam(name="limit", requirements="\d+", nullable=true, description="Number of items per page. defaults to 10.")
     * @AclAncestor("oro_user_group_view")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cgetAction()
    {
        $page = (int) $this->getRequest()->get('page', 1);
        $limit = (int) $this->getRequest()->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * Get group data
     *
     * @param int $id Group id
     *
     * @ApiDoc(
     *      description="Get group data",
     *      resource=true,
     *      filters={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @AclAncestor("oro_user_group_view")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * Create new group
     *
     * @ApiDoc(
     *      description="Create new group",
     *      resource=true
     * )
     * @AclAncestor("oro_user_group_create")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * Update existing group
     *
     * @param int $id Group id
     *
     * @ApiDoc(
     *      description="Update existing group",
     *      resource=true,
     *      filters={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @AclAncestor("oro_user_group_update")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Delete group
     *
     * @param int $id Group id
     *
     * @ApiDoc(
     *      description="Delete group",
     *      resource=true,
     *      filters={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @Acl(
     *      id="oro_user_group_remove",
     *      type="entity",
     *      class="OroUserBundle:Group",
     *      permission="DELETE"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * Get group roles
     *
     * @param int $id Group id
     *
     * @ApiDoc(
     *      description="Get group roles",
     *      resource=true,
     *      filters={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @AclAncestor("oro_user_group_view")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    /*public function getRolesAction($id)
    {
        $entity = $this->getManager()->find($id);

        if (!$entity) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        return $this->handleView($this->view($entity->getRoles(), Codes::HTTP_OK));
    }*/

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

<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Rest;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\QueryParam;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;

/**
 * @NamePrefix("oro_api_")
 */
class RoleController extends RestController implements ClassResourceInterface
{
    /**
     * Get the list of roles
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @ApiDoc(
     *      description="Get the list of roles",
     *      resource=true
     * )
     * @QueryParam(
     *      name="page",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Page number, starting from 1. Defaults to 1."
     * )
     * @QueryParam(
     *      name="limit",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Number of items per page. defaults to 10."
     * )
     * @AclAncestor("oro_user_role_view")
     */
    public function cgetAction()
    {
        $page = (int) $this->getRequest()->get('page', 1);
        $limit = (int) $this->getRequest()->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * Get role data
     *
     * @param int $id Role id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @ApiDoc(
     *      description="Get role data",
     *      resource=true,
     *      filters={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @AclAncestor("oro_user_role_view")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * Create new role
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @ApiDoc(
     *      description="Create new role",
     *      resource=true
     * )
     * @AclAncestor("oro_user_role_create")
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * Update existing role
     *
     * @param int $id Role id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @ApiDoc(
     *      description="Update existing role",
     *      resource=true,
     *      filters={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @AclAncestor("oro_user_role_update")
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Delete role
     *
     * @param int $id Role id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @ApiDoc(
     *      description="Delete role",
     *      resource=true,
     *      filters={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @Acl(
     *      id="oro_user_role_remove",
     *      type="entity",
     *      class="OroUserBundle:Role",
     *      permission="DELETE"
     * )
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * Get role by name
     *
     * @param string $name Role name
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @ApiDoc(
     *      description="Get role by name",
     *      resource=true,
     *      filters={
     *          {"name"="name", "dataType"="string"},
     *      }
     * )
     * @AclAncestor("oro_user_role_view")
     */
    public function getBynameAction($name)
    {
        $entity = $this->getManager()->getRepository()->findOneBy(array('label' => $name));

        return $this->handleView(
            $this->view(
                $entity,
                $entity ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_user.role_manager.api');
    }

    /**
     * @inheritdoc
     */
    public function getForm()
    {
        return $this->get('oro_user.form.role.api');
    }

    /**
     * @inheritdoc
     */
    public function getFormHandler()
    {
        return $this->get('oro_user.form.handler.role.api');
    }

    /**
     * @inheritdoc
     */
    protected function handleDelete($entity, ObjectManager $em)
    {
        parent::handleDelete($entity, $em);
        /** @var \Oro\Bundle\SecurityBundle\Acl\Persistence\AclSidManager $aclSidManager */
        $aclSidManager = $this->get('oro_security.acl.sid_manager');
        if ($aclSidManager->isAclEnabled()) {
            $aclSidManager->deleteSid($aclSidManager->getSid($entity));
        }
    }
}

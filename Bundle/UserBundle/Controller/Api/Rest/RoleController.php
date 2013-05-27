<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Rest;

use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\QueryParam;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;
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
     * @QueryParam(name="page", requirements="\d+", nullable=true, description="Page number, starting from 1. Defaults to 1.")
     * @QueryParam(name="limit", requirements="\d+", nullable=true, description="Number of items per page. defaults to 10.")
     * @AclAncestor("oro_user_role_list")
     */
    public function cgetAction()
    {
        $page = (int)$this->getRequest()->get('page', 1);
        $limit = (int)$this->getRequest()->get('limit', self::ITEMS_PER_PAGE);

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
     * @Acl(
     *      id="oro_user_role_show",
     *      name="View role",
     *      description="View role",
     *      parent="oro_user_role"
     * )
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
     *      name="Remove role",
     *      description="Remove role",
     *      parent="oro_user_role"
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
     * @AclAncestor("oro_user_role_show")
     */
    public function getBynameAction($name)
    {
        $entity = $this->getManager()->getRepository()->findOneBy(array('role' => $name));

        return $this->handleView(
            $this->view(
                $entity,
                $entity ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND
            )
        );
    }

    /**
     * Get ACL resources granted by a role
     *
     * @param int $id User id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @ApiDoc(
     *      description="Get ACL resources granted by a role",
     *      resource=true,
     *      requirements={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @AclAncestor("oro_user_acl_edit")
     */
    public function getAclAction($id)
    {
        $role = $this->getManager()->find($id);

        if (!$role) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        return $this->handleView(
            $this->view(
                $this->get('oro_user.acl_manager')->getAllowedAclResourcesForRoles(array($role)),
                Codes::HTTP_OK
            )
        );
    }

    /**
     * Link ACL resource to role
     *
     * @param int    $id       Role id
     * @param string $resource ACL resource id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @ApiDoc(
     *      description="Link ACL resource to role",
     *      requirements={
     *          {"name"="id", "dataType"="integer"},
     *          {"name"="resource", "dataType"="string"},
     *      }
     * )
     * @AclAncestor("oro_user_acl_save")
     */
    public function postAclAction($id, $resource)
    {
        $this->getAclManager()->modifyAclForRole($id, $resource, true);

        return $this->handleView($this->view('', Codes::HTTP_NO_CONTENT));
    }

    /**
     * Unlink ACL resource from role
     *
     * @param int    $id       Role id
     * @param string $resource ACL resource id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @ApiDoc(
     *      description="Unlink ACL resource from role",
     *      requirements={
     *          {"name"="id", "dataType"="integer"},
     *          {"name"="resource", "dataType"="string"},
     *      }
     * )
     * @AclAncestor("oro_user_acl_save")
     */
    public function deleteAclAction($id, $resource)
    {
        $this->getAclManager()->modifyAclForRole($id, $resource, false);

        return $this->handleView($this->view('', Codes::HTTP_NO_CONTENT));
    }

    /**
     * Link ACL resources to role
     *
     * @param int $id Role id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @QueryParam(name="resources", nullable=false, description="Array of ACL resource ids")
     * @ApiDoc(
     *      description="Link ACL resources to role",
     *      requirements={
     *          {"name"="id", "dataType"="integer"}
     *      }
     * )
     * @AclAncestor("oro_user_acl_save")
     */
    public function postAclArrayAction($id)
    {
        $this->getAclManager()->modifyAclsForRole(
            $id,
            $this->getRequest()->request->get('resources'),
            true
        );

        return $this->handleView($this->view('', Codes::HTTP_CREATED));
    }

    /**
     * Unlink ACL resources from role
     *
     * @param int $id Role id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @QueryParam(name="resources", nullable=false, description="Array of ACL resource ids")
     * @ApiDoc(
     *      description="Unlink ACL resources from roles",
     *      requirements={
     *          {"name"="id", "dataType"="integer"}
     *      }
     * )
     * @AclAncestor("oro_user_acl_save")
     */
    public function deleteAclArrayAction($id)
    {
        $this->getAclManager()->modifyAclsForRole(
            $id,
            $this->getRequest()->request->get('resources'),
            false
        );

        return $this->handleView($this->view('', Codes::HTTP_NO_CONTENT));
    }

    /**
     * @return \Oro\Bundle\UserBundle\Acl\Manager
     */
    protected function getAclManager()
    {
        return $this->get('oro_user.acl_manager');
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
}

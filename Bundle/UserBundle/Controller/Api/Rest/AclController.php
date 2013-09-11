<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Rest;

use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\Acl;

/**
 * @NamePrefix("oro_api_")
 */
class AclController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Get ACL resources
     *
     * @ApiDoc(
     *      description="Get ACL resources",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_user_acl_edit",
     *      label="View ACL tree for a particular role",
     *      type="action",
     *      group_name=""
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cgetAction()
    {
        return $this->handleView(
            $this->view(
                $this->get('oro_user.acl_manager')->getAclResources(false),
                Codes::HTTP_OK
            )
        );
    }

    /**
     * Get ACL resource data
     *
     * @param string $id ACL resource id
     *
     * @ApiDoc(
     *      description="Get ACL resource data",
     *      resource=true,
     *      filters={
     *          {"name"="id", "dataType"="string"},
     *      },
     *      requirements={
     *          {"name"="id", "dataType"="string"},
     *      }
     * )
     * @Acl(
     *      id="oro_user_acl_show",
     *      label="View ACL resource",
     *      type="action",
     *      group_name=""
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction($id)
    {
        $resource = $this->get('oro_user.acl_manager')->getAclResource($id);

        return $this->handleView(
            $this->view(
                $resource? $resource->toArray() : '',
                $resource ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND
            )
        );
    }
}

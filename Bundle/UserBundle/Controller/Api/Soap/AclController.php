<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Soap;

use Symfony\Component\DependencyInjection\ContainerAware;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;

class AclController extends ContainerAware
{
    /**
     * Get ACL Resources
     *
     * @Soap\Method("getAclIds")
     * @Soap\Result(phpType = "string[]")
     * @AclAncestor("oro_user_acl_edit")
     */
    public function cgetAction()
    {
        return $this->getManager()->getAclResources(false);
    }

    /**
     * @Soap\Method("getAcl")
     * @Soap\Param("id", phpType = "string")
     * @Soap\Result(phpType = "Oro\Bundle\UserBundle\Entity\Acl")
     * @AclAncestor("oro_user_acl_show")
     */
    public function getAction($id)
    {
        $resource = $this->getManager()->getAclResource($id);

        if (!$resource) {
            throw new \SoapFault('NOT_FOUND', sprintf('Acl resource with id "%s" cannot be found', $id));
        }

        return $resource;
    }

    /**
     * @return \Oro\Bundle\UserBundle\Acl\Manager
     */
    public function getManager()
    {
        return $this->container->get('oro_user.acl_manager');
    }
}

<?php

namespace Oro\Bundle\AddressBundle\Controller\Api\Soap;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;

class AddressTypeController extends Controller
{
    /**
     * @Soap\Method("getAddressTypes")
     * @Soap\Result(phpType = "Oro\Bundle\AddressBundle\Entity\AddressType[]")
     * @AclAncestor("oro_address_type_list")
     */
    public function cgetAction()
    {
        return $this->getDoctrine()->getRepository('OroAddressBundle:AddressType')->findAll();
    }

    /**
     * @Soap\Method("getAddressType")
     * @Soap\Param("name", phpType = "string")
     * @Soap\Result(phpType = "Oro\Bundle\AddressBundle\Entity\AddressType")
     * @AclAncestor("oro_address_type_show")
     */
    public function getAction($name)
    {
        $entity = $this->getDoctrine()->getRepository('OroAddressBundle:AddressType')->find($name);

        if (!$entity) {
            throw new \SoapFault('NOT_FOUND', sprintf('Address type "%s" can\'t be found', $name));
        }

        return $entity;
    }
}

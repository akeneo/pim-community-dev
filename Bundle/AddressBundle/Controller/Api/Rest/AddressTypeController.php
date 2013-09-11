<?php

namespace Oro\Bundle\AddressBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\AddressBundle\Entity\AddressType;

/**
 * @RouteResource("addresstype")
 * @NamePrefix("oro_api_")
 */
class AddressTypeController extends FOSRestController implements ClassResourceInterface
{
    /**
     * REST GET list
     *
     * @ApiDoc(
     *      description="Get all address types items",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_address_type_list",
     *      type="entity",
     *      class="OroAddressBundle:Address",
     *      permission="VIEW"
     * )
     * @return Response
     */
    public function cgetAction()
    {
        $items = $this->getDoctrine()->getRepository('OroAddressBundle:AddressType')->findAll();

        return $this->handleView(
            $this->view($items, is_array($items) ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND)
        );
    }

    /**
     * REST GET item
     *
     * @param string $name
     *
     * @ApiDoc(
     *      description="Get address type item",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_address_type_show",
     *      type="entity",
     *      class="OroAddressBundle:Address",
     *      permission="VIEW"
     * )
     * @return Response
     */
    public function getAction($name)
    {
        if (!$name) {
            return $this->handleView($this->view(null, Codes::HTTP_NOT_FOUND));
        }

        /** @var $item AddressType */
        $item = $this->getDoctrine()->getRepository('OroAddressBundle:AddressType')->find($name);

        return $this->handleView(
            $this->view($item, is_object($item) ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND)
        );
    }
}

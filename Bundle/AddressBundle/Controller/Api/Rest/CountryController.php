<?php

namespace Oro\Bundle\AddressBundle\Controller\Api\Rest;

use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\Response;

/**
 * @RouteResource("country")
 * @NamePrefix("oro_api_")
 */
class CountryController extends FOSRestController
{
    /**
     * Get countries
     *
     * @ApiDoc(
     *  description="Get countries",
     *  resource=true
     * )
     * @AclAncestor("oro_address")
     * @return Response
     */
    public function cgetAction()
    {
        $items = $this->getDoctrine()->getRepository('OroAddressBundle:Country')->findAll();

        return $this->handleView(
            $this->view($items, is_array($items) ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND)
        );
    }

    /**
     * REST GET country
     *
     * @param string $id
     *
     * @ApiDoc(
     *  description="Get country",
     *  resource=true
     * )
     * @AclAncestor("oro_address")
     * @return Response
     */
    public function getAction($id)
    {
        $item = $this->getDoctrine()->getRepository('OroAddressBundle:Country')->find($id);

        return $this->handleView(
            $this->view($item, is_object($item) ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND)
        );
    }
}

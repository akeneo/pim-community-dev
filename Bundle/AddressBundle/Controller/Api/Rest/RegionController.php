<?php

namespace Oro\Bundle\AddressBundle\Controller\Api\Rest;

use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * @RouteResource("region")
 * @NamePrefix("oro_api_")
 */
class RegionController extends FOSRestController
{
    /**
     * Get regions
     *
     * @ApiDoc(
     *  description="Get regions",
     *  resource=true
     * )
     * @return Response
     */
    public function cgetAction()
    {
        /** @var  $item \Oro\Bundle\AddressBundle\Entity\Region */
        $items = $this->getDoctrine()->getRepository('OroAddressBundle:Region')->findAll();

        return $this->handleView(
            $this->view($items, is_array($items) ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND)
        );
    }

    /**
     * REST GET region by id
     *
     * @param string $id
     *
     * @ApiDoc(
     *  description="Get region by id",
     *  resource=true
     * )
     * @return Response
     */
    public function getAction($id)
    {
        /** @var  $item \Oro\Bundle\AddressBundle\Entity\Region */
        $item = $this->getDoctrine()->getRepository('OroAddressBundle:Region')->find($id);

        return $this->handleView(
            $this->view($item, is_object($item) ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND)
        );
    }
}

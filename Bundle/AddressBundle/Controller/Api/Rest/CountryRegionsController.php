<?php

namespace Oro\Bundle\AddressBundle\Controller\Api\Rest;

use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * @RouteResource("country/regions")
 * @NamePrefix("oro_api_country_")
 */
class CountryRegionsController extends FOSRestController
{
    /**
     * REST GET regions by country
     *
     * @param string $id
     *
     * @ApiDoc(
     *  description="Get regions by country id",
     *  resource=true
     * )
     * @return Response
     */
    public function getAction($id)
    {
        /** @var  $item \Oro\Bundle\AddressBundle\Entity\Country */
        $item = $this->getDoctrine()->getRepository('OroAddressBundle:Country')->find($id);

        return $this->handleView(
            $this->view($item ? $item->getRegions() : null, $item ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND)
        );
    }
}

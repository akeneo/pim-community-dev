<?php

namespace Oro\Bundle\AddressBundle\Controller\Api\Rest;

use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * @RouteResource("region")
 * @NamePrefix("oro_api_")
 * TODO: Discuss ACL impl.
 */
class RegionController extends FOSRestController
{
    /**
     * REST GET region by id
     *
     * @QueryParam(name="id", nullable=false)
     *
     * @ApiDoc(
     *     description="Get region by id",
     *     resource=true,
     *     requirements={
     *         {"name"="id", "dataType"="string", "requirement"="\d+", "description"="region combined code"}
     *     }
     * )
     * @return Response
     */
    public function getAction()
    {
        $id = $this->getRequest()->get('id');
        if (!$id) {
            return $this->handleView($this->view(null, Codes::HTTP_NOT_FOUND));
        }

        /** @var  $item \Oro\Bundle\AddressBundle\Entity\Region */
        $item = $this->getDoctrine()->getRepository('OroAddressBundle:Region')->find($id);

        return $this->handleView(
            $this->view($item, is_object($item) ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND)
        );
    }
}

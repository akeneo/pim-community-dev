<?php

namespace Oro\Bundle\EntityBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\Rest\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\EntityBundle\Provider\EntityProvider;

/**
 * @RouteResource("entity")
 * @NamePrefix("oro_api_")
 */
class EntityController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Get entities.
     *
     * @ApiDoc(
     *      description="Get entities",
     *      resource=true
     * )
     *
     * @return Response
     */
    public function cgetAction()
    {
        /** @var EntityProvider $provider */
        $provider = $this->get('oro_entity.entity_provider');
        $result = $provider->getEntities(false);

        return $this->handleView($this->view($result, Codes::HTTP_OK));
    }
}

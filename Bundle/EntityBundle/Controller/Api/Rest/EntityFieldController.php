<?php

namespace Oro\Bundle\EntityBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\Rest\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;
use Oro\Bundle\EntityBundle\Exception\InvalidEntityException;

/**
 * @RouteResource("entity")
 * @NamePrefix("oro_api_")
 */
class EntityFieldController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Get entity fields.
     *
     * @param string $entityName Entity full class name; backslashes (\) should be replaced with underscore (_).
     *
     * @QueryParam(
     *      name="with-relations", requirements="(true)|(false)", nullable=true, strict=true, default="false",
     *      description="Indicates whether fields of related entities should be returned as well.")
     * @QueryParam(
     *      name="with-entity-details", requirements="(true)|(false)", nullable=true, strict=true, default="false",
     *      description="Indicates whether details of related entity should be returned as well.")
     * @QueryParam(
     *      name="deep-level", requirements="\d+", nullable=true, strict=true, default="0",
     *      description="The maximum deep level of related entities.")
     * @QueryParam(
     *      name="last-deep-level-relations", requirements="(true)|(false)",
     *      nullable=true, strict=true, default="false",
     *      description="Indicates whether fields for the last deep level of related entities should be returned.")
     * @Get(name="oro_api_get_entity_fields", requirements={"entityName"="((\w+)_)+(\w+)"})
     * @ApiDoc(
     *      description="Get entity fields",
     *      resource=true
     * )
     *
     * @return Response
     */
    public function getFieldsAction($entityName)
    {
        $entityName        = str_replace('_', '\\', $entityName);
        $withRelations     = ('true' === $this->getRequest()->query->get('with-relations'));
        $withEntityDetails = ('true' === $this->getRequest()->query->get('with-entity-details'));
        $deepLevel         = $this->getRequest()->query->has('deep-level')
            ? (int)$this->getRequest()->query->get('deep-level')
            : 0;
        $lastDeepLevelRelations = ('true' === $this->getRequest()->query->get('last-deep-level-relations'));

        $statusCode = Codes::HTTP_OK;
        /** @var EntityFieldProvider $provider */
        $provider = $this->get('oro_entity.entity_field_provider');
        try {
            $result = $provider->getFields(
                $entityName,
                $withRelations,
                $withEntityDetails,
                $deepLevel,
                $lastDeepLevelRelations
            );
        } catch (InvalidEntityException $ex) {
            $statusCode = Codes::HTTP_NOT_FOUND;
            $result     = array('message' => $ex->getMessage());
        }

        return $this->handleView($this->view($result, $statusCode));
    }
}

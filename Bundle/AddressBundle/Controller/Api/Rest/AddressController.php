<?php

namespace Oro\Bundle\AddressBundle\Controller\Api\Rest;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\AddressBundle\Entity\Manager\AddressManager;

/**
 * @RouteResource("address")
 * @NamePrefix("oro_api_")
 * TODO: Discuss ACL impl.
 */
class AddressController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET list
     *
     * @QueryParam(
     *      name="page",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Page number, starting from 1. Defaults to 1."
     * )
     * @QueryParam(
     *      name="limit",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Number of items per page. defaults to 10."
     * )
     * @ApiDoc(
     *      description="Get all addresses items",
     *      resource=true
     * )
     * filters={
     *      {"name"="page", "dataType"="integer"},
     *      {"name"="limit", "dataType"="integer"}
     *  }
     * Acl(
     *      id="oro_address_list",
     *      type="action",
     *      label="API Get Addresses",
     *      group_name=""
     * )
     * @return Response
     */
    public function cgetAction()
    {
        $page = (int)$this->getRequest()->get('page', 1);
        $limit = (int)$this->getRequest()->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * REST GET item
     *
     * @param string $id
     *
     * @ApiDoc(
     *      description="Get address item",
     *      resource=true
     * )
     * Acl(
     *      id="oro_address_show",
     *      type="action",
     *      label="API Get Address",
     *      group_name=""
     * )
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * REST PUT
     *
     * @param int $id Address item id
     *
     * @ApiDoc(
     *      description="Update address",
     *      resource=true
     * )
     * Acl(
     *      id="oro_address_edit",
     *      type="action",
     *      label="API Edit Addresses",
     *      group_name=""
     * )
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new address
     *
     * @ApiDoc(
     *      description="Create new address",
     *      resource=true
     * )
     * Acl(
     *      id="oro_address_create",
     *      type="action",
     *      label="API Create Addresses",
     *      group_name=""
     * )
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Remove Address",
     *      resource=true
     * )
     * Acl(
     *      id="oro_address_remove",
     *      type="action",
     *      label="API Delete Addresses",
     *      group_name=""
     * )
     * @return Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * Get entity Manager
     *
     * @return AddressManager
     */
    public function getManager()
    {
        return $this->get('oro_address.address.manager.api');
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->get('oro_address.form.address.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('oro_address.form.handler.address.api');
    }

    protected function transformEntityField($field, &$value)
    {
        switch ($field) {
            case 'country':
                $value = array(
                    'iso2code' => $value->getIso2Code(),
                    'iso3code' => $value->getIso3Code(),
                    'name' => $value->getName()
                );
                break;
            case 'state':
                $value = array(
                    'code' => $value->getCode(),
                    'name' => $value->getName()
                );
                break;
            default:
                parent::transformEntityField($field, $value);
        }
    }
}

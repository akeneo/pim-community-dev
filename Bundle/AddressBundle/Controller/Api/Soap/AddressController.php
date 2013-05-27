<?php

namespace Oro\Bundle\AddressBundle\Controller\Api\Soap;

use Symfony\Component\Form\FormInterface;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\SoapBundle\Controller\Api\Soap\FlexibleSoapController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiFlexibleEntityManager;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;

class AddressController extends FlexibleSoapController
{
    /**
     * @Soap\Method("getAddresses")
     * @Soap\Param("page", phpType="int")
     * @Soap\Param("limit", phpType="int")
     * @Soap\Result(phpType = "Oro\Bundle\AddressBundle\Entity\Address[]")
     */
    public function cgetAction($page = 1, $limit = 10)
    {
        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * @Soap\Method("getAddress")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\AddressBundle\Entity\Address")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * @Soap\Method("createAddress")
     * @Soap\Param("address", phpType = "Oro\Bundle\AddressBundle\Entity\AddressSoap")
     * @Soap\Result(phpType = "boolean")
     */
    public function createAction($address)
    {
        return $this->handleCreateRequest();
    }

    /**
     * @Soap\Method("updateAddress")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Param("address", phpType = "Oro\Bundle\AddressBundle\Entity\AddressSoap")
     * @Soap\Result(phpType = "boolean")
     */
    public function updateAction($id, $address)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * @Soap\Method("deleteAddress")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "boolean")
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * @return ApiFlexibleEntityManager
     */
    public function getManager()
    {
        return $this->container->get('oro_address.address.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->container->get('oro_address.form.address.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->container->get('oro_address.form.handler.address.api');
    }
}

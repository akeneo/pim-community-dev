<?php
namespace Oro\Bundle\OrganizationBundle\Controller\Api\Soap;

use Symfony\Component\Form\FormInterface;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\SoapBundle\Controller\Api\Soap\SoapController;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class BusinessUnitController extends SoapController
{
    /**
     * @Soap\Method("getBusinessUnits")
     * @Soap\Param("page", phpType="int")
     * @Soap\Param("limit", phpType="int")
     * @Soap\Result(phpType = "Oro\Bundle\OrganizationBundle\Entity\BusinessUnit[]")
     * @AclAncestor("oro_business_unit_view")
     */
    public function cgetAction($page = 1, $limit = 10)
    {
        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * @Soap\Method("getBusinessUnit")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\OrganizationBundle\Entity\BusinessUnit")
     * @AclAncestor("oro_business_unit_view")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * @Soap\Method("createBusinessUnit")
     * @Soap\Param("business_unit", phpType = "Oro\Bundle\OrganizationBundle\Entity\BusinessUnit")
     * @Soap\Result(phpType = "int")
     * @AclAncestor("oro_business_unit_create")
     */
    public function createAction($business_unit)
    {
        return $this->handleCreateRequest();
    }

    /**
     * @Soap\Method("updateBusinessUnit")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Param("business_unit", phpType = "Oro\Bundle\OrganizationBundle\Entity\BusinessUnit")
     * @Soap\Result(phpType = "boolean")
     * @AclAncestor("oro_business_unit_update")
     */
    public function updateAction($id, $business_unit)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * @Soap\Method("deleteBusinessUnit")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "boolean")
     * @AclAncestor("oro_business_unit_delete")
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->container->get('oro_organization.business_unit.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->container->get('oro_organization.form.business_unit.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->container->get('oro_organization.form.handler.api');
    }
}

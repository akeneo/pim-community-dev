<?php

namespace Oro\Bundle\OrganizationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Datagrid\BusinessUnitUpdateUserDatagridManager;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;

/**
 * @Route("/business_unit")
 * @Acl(
 *      id="oro_business_unit",
 *      name="Business Unit manipulation",
 *      description="Business Unit manipulation"
 * )
 */
class BusinessUnitController extends Controller
{
    /**
     * Create business_unit form
     *
     * @Route("/create", name="oro_business_unit_create")
     * @Template("OroOrganizationBundle:BusinessUnit:update.html.twig")
     * @Acl(
     *      id="oro_business_unit_create",
     *      name="Create business_unit",
     *      description="Create new business_unit",
     *      parent="oro_business_unit"
     * )
     */
    public function createAction()
    {
        return $this->updateAction(new BusinessUnit());
    }

    /**
     * @Route("/view/{id}", name="oro_business_unit_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_business_unit_view",
     *      name="View business unit",
     *      description="View business unit",
     *      parent="oro_business_unit"
     * )
     */
    public function viewAction(BusinessUnit $entity)
    {
        return array(
            'datagrid' => $this->getBusinessUnitDatagridManager($entity, 'view')->getDatagrid()->createView(),
            'entity' => $entity,
        );
    }

    /**
     * Edit business_unit form
     *
     * @Route("/update/{id}", name="oro_business_unit_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="oro_business_unit_update",
     *      name="Edit business_unit",
     *      description="Edit business_unit",
     *      parent="oro_business_unit"
     * )
     */
    public function updateAction(BusinessUnit $entity)
    {
        if ($this->get('oro_organization.form.handler.business_unit')->process($entity)) {
            $this->get('session')->getFlashBag()->add('success', 'Business Unit successfully saved');

            return $this->get('oro_ui.router')->actionRedirect(
                array(
                    'route' => 'oro_business_unit_update',
                    'parameters' => array('id' => $entity->getId()),
                ),
                array(
                    'route' => 'oro_business_unit_index',
                )
            );
        }

        return array(
            'datagrid' => $this->getBusinessUnitDatagridManager($entity, 'update')->getDatagrid()->createView(),
            'form'     => $this->get('oro_organization.form.business_unit')->createView(),
        );
    }
    
    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_business_unit_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id="oro_business_unit_list",
     *      name="View business_unit list",
     *      description="List of business_units",
     *      parent="oro_business_unit"
     * )
     */
    public function indexAction(Request $request)
    {
        $datagrid = $this->get('oro_organization.business_unit_datagrid_manager')->getDatagrid();
        $view     = 'json' == $request->getRequestFormat()
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'OroOrganizationBundle:BusinessUnit:index.html.twig';

        return $this->render(
            $view,
            array('datagrid' => $datagrid->createView())
        );
    }

    /**
     * Get grid users data
     *
     * @Route(
     *      "/update_grid/{id}",
     *      name="oro_business_update_unit_user_grid",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0, "_format"="json"}
     * )
     * @Template("OroGridBundle:Datagrid:list.json.php")
     * @AclAncestor("oro_business_unit_list")
     */
    public function updateGridDataAction(BusinessUnit $entity = null)
    {
        if (!$entity) {
            $entity = new BusinessUnit();
        }

        return array('datagrid' => $this->getBusinessUnitDatagridManager($entity, 'update')
            ->getDatagrid()->createView());
    }

    /**
     * Get grid users data
     *
     * @Route(
     *      "/view_grid/{id}",
     *      name="oro_business_view_unit_user_grid",
     *      requirements={"id"="\d+"},
     *      defaults={"_format"="json"}
     * )
     * @Template("OroGridBundle:Datagrid:list.json.php")
     * @AclAncestor("oro_business_unit_list")
     */
    public function viewGridDataAction(BusinessUnit $entity)
    {
        return array('datagrid' => $this->getBusinessUnitDatagridManager($entity, 'view')->getDatagrid()->createView());
    }

    /**
     * @param  BusinessUnit $businessUnit
     * @param  string       $action
     * @return BusinessUnitUpdateUserDatagridManager
     */
    protected function getBusinessUnitDatagridManager(BusinessUnit $businessUnit, $action)
    {
        /** @var $result BusinessUnitUpdateUserDatagridManager */
        $result = $this->get('oro_organization.business_unit_' . $action . '_user_datagrid_manager');
        $result->setBusinessUnit($businessUnit);
        $result->getRouteGenerator()->setRouteParameters(array('id' => $businessUnit->getId()));

        return $result;
    }
}

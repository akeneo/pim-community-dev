<?php

namespace Oro\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\UserBundle\Datagrid\RoleUserDatagridManager;

/**
 * @Route("/role")
 */
class RoleController extends Controller
{
    /**
     * @Acl(
     *      id="oro_user_role_create",
     *      type="entity",
     *      class="OroUserBundle:Role",
     *      permission="CREATE"
     * )
     * @Route("/create", name="oro_user_role_create")
     * @Template("OroUserBundle:Role:update.html.twig")
     */
    public function createAction()
    {
        return $this->update(new Role());
    }

    /**
     * @Acl(
     *      id="oro_user_role_update",
     *      type="entity",
     *      class="OroUserBundle:Role",
     *      permission="EDIT"
     * )
     * @Route("/update/{id}", name="oro_user_role_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     */
    public function updateAction(Role $entity)
    {
        return $this->update($entity);
    }

    /**
     * Get grid users data
     *
     * @Route(
     *      "/grid/{id}",
     *      name="oro_user_role_user_grid",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0, "_format"="json"}
     * )
     * @Template("OroGridBundle:Datagrid:list.json.php")
     * @AclAncestor("oro_user_user_view")
     */
    public function gridDataAction(Role $entity = null)
    {
        if (!$entity) {
            $entity = new Role();
        }

        return array('datagrid' => $this->getRoleUserDatagridManager($entity)->getDatagrid()->createView());
    }

    /**
     * @param  Role                    $role
     * @return RoleUserDatagridManager
     */
    protected function getRoleUserDatagridManager(Role $role)
    {
        /** @var $result RoleUserDatagridManager */
        $result = $this->get('oro_user.role_user_datagrid_manager');
        $result->setRole($role);
        $result->getRouteGenerator()->setRouteParameters(array('id' => $role->getId()));

        return $result;
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_user_role_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id="oro_user_role_view",
     *      type="entity",
     *      class="OroUserBundle:Role",
     *      permission="VIEW"
     * )
     */
    public function indexAction(Request $request)
    {
        $datagrid = $this->get('oro_user.role_datagrid_manager')->getDatagrid();
        $view     = 'json' == $request->getRequestFormat()
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'OroUserBundle:Role:index.html.twig';

        return $this->render(
            $view,
            array('datagrid' => $datagrid->createView())
        );
    }

    /**
     * @param Role $entity
     * @return array
     */
    protected function update(Role $entity)
    {
        $aclRoleHandler = $this->get('oro_user.form.handler.acl_role');
        $aclRoleHandler->createForm($entity);

        if ($aclRoleHandler->process($entity)) {

            $this->get('session')->getFlashBag()->add('success', 'Role successfully saved');

            return $this->get('oro_ui.router')->actionRedirect(
                array(
                    'route' => 'oro_user_role_update',
                    'parameters' => array('id' => $entity->getId()),
                ),
                array(
                    'route' => 'oro_user_role_index',
                )
            );
        }

        return array(
            'form'     => $aclRoleHandler->createView(),
            'privilegesConfig' => $this->container->getParameter('oro_user.privileges'),
            'datagrid' => $this->getRoleUserDatagridManager($entity)->getDatagrid()->createView(),
        );
    }
}

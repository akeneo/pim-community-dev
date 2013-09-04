<?php

namespace Oro\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;
use Oro\Bundle\UserBundle\Datagrid\RoleUserDatagridManager;

use Oro\Bundle\UserBundle\Form\Type\NewRoleType;

/**
 * @Route("/role")
 * @Acl(
 *      id="oro_user_role",
 *      name="Role manipulation",
 *      description="Role manipulation"
 * )
 */
class RoleController extends Controller
{
    /**
     * @Route("/create", name="oro_user_new_role_create")
     * @Template("OroUserBundle:Role:updateNew.html.twig")
     */
    public function createNewAction()
    {
        return $this->updateNewAction(new Role());
    }

    /**
     * @Route("/update-new/{id}", name="oro_user_new_role_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     */
    public function updateNewAction(Role $entity)
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
        );





        /****************************************/
        /*$permissionList = array(
            'VIEW',
            'CREATE',
            'EDIT',
            'DELETE'
        );
        $form = $this->createForm(new NewRoleType($permissionList), $entity);*/



        /** @var $entityField \Oro\Bundle\SecurityBundle\Form\Type\CollectionType */
        /*$entityCollection = $form->get('entities');

        $entityCollection->setData($dataArray);
*/

        /**
         * @todo: work with form must be in form handler
         */
        /*if ($this->getRequest()->isMethod('POST')) {
            $request = $this->getRequest();
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->getAclManager()->saveNewRoleAcl($entity, $form);

                $this->get('session')->getFlashBag()->add('success', 'Role successfully saved');

                return $this->get('oro_ui.router')->actionRedirect(
                    array(
                        'route' => 'oro_user_new_role_update',
                        'parameters' => array('id' => $entity->getId()),
                    ),
                    array(
                        'route' => 'oro_user_role_index',
                    )
                );
            }
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );*/
    }

    /**
     * Create role form
     *
     * @Route("/create", name="oro_user_role_create")
     * @Template("OroUserBundle:Role:update.html.twig")
     * @Acl(
     *      id="oro_user_role_create",
     *      name="Create role",
     *      description="Create new role",
     *      parent="oro_user_role"
     * )
     */
    public function createAction()
    {
        return $this->updateAction(new Role());
    }

    /**
     * Edit role form
     *
     * @Route("/update/{id}", name="oro_user_role_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="oro_user_role_update",
     *      name="Edit role",
     *      description="Edit role",
     *      parent="oro_user_role"
     * )
     */
    public function updateAction(Role $entity)
    {
        $resources = $this->getRequest()->request->get('resource');
        if ($this->get('oro_user.form.handler.role')->process($entity)) {
            $this->getAclManager()->saveRoleAcl($entity, $resources);

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
            'datagrid' => $this->getRoleUserDatagridManager($entity)->getDatagrid()->createView(),
            'form'     => $this->get('oro_user.form.role')->createView(),
            'resources' => $this->getAclManager()->getRoleAclTree($entity)
        );
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
     * @AclAncestor("oro_user_role_list")
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
     *      id="oro_user_role_list",
     *      name="View role list",
     *      description="List of roles",
     *      parent="oro_user_role"
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
     * @return \Oro\Bundle\UserBundle\Acl\Manager
     */
    protected function getAclManager()
    {
        return $this->get('oro_user.acl_manager');
    }
}

<?php

namespace Oro\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\UserBundle\Datagrid\GroupUserDatagridManager;

/**
 * @Route("/group")
 */
class GroupController extends Controller
{
    /**
     * Create group form
     *
     * @Route("/create", name="oro_user_group_create")
     * @Template("OroUserBundle:Group:update.html.twig")
     * @Acl(
     *      id="oro_user_group_create",
     *      type="entity",
     *      class="OroUserBundle:Group",
     *      permission="CREATE"
     * )
     */
    public function createAction()
    {
        return $this->update(new Group());
    }

    /**
     * Edit group form
     *
     * @Route("/update/{id}", name="oro_user_group_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="oro_user_group_update",
     *      type="entity",
     *      class="OroUserBundle:Group",
     *      permission="EDIT"
     * )
     */
    public function updateAction(Group $entity)
    {
        return $this->update($entity);
    }

    /**
     * Get grid data
     *
     * @Route(
     *      "/grid/{id}",
     *      name="oro_user_group_user_grid",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0, "_format"="json"}
     * )
     * @AclAncestor("oro_user_user_view")
     */
    public function gridDataAction(Group $entity = null)
    {
        if (!$entity) {
            $entity = new Group();
        }

        $datagridView = $this->getGroupUserDatagridManager($entity)->getDatagrid()->createView();

        return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
    }

    /**
     * @param  Group                    $group
     * @return GroupUserDatagridManager
     */
    protected function getGroupUserDatagridManager(Group $group)
    {
        /** @var $result GroupUserDatagridManager */
        $result = $this->get('oro_user.group_user_datagrid_manager');
        $result->setGroup($group);
        $result->getRouteGenerator()->setRouteParameters(array('id' => $group->getId()));

        return $result;
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_user_group_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id="oro_user_group_view",
     *      type="entity",
     *      class="OroUserBundle:Group",
     *      permission="VIEW"
     * )
     */
    public function indexAction(Request $request)
    {
        $view = $this->get('oro_user.group_datagrid_manager')->getDatagrid()->createView();

        return 'json' == $this->getRequest()->getRequestFormat()
            ? $this->get('oro_grid.renderer')->renderResultsJsonResponse($view)
            : $this->render('OroUserBundle:Group:index.html.twig', array('datagrid' => $view));
    }

    /**
     * @param Group $entity
     * @return array
     */
    protected function update(Group $entity)
    {
        if ($this->get('oro_user.form.handler.group')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('oro.user.controller.group.message.saved')
            );

            if (!$this->getRequest()->get('_widgetContainer')) {

                return $this->get('oro_ui.router')->actionRedirect(
                    array(
                        'route' => 'oro_user_group_update',
                        'parameters' => array('id' => $entity->getId()),
                    ),
                    array(
                        'route' => 'oro_user_group_index',
                    )
                );
            }
        }

        return array(
            'datagrid' => $this->getGroupUserDatagridManager($entity)->getDatagrid()->createView(),
            'form'     => $this->get('oro_user.form.group')->createView(),
        );
    }
}

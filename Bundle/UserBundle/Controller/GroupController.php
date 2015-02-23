<?php

namespace Oro\Bundle\UserBundle\Controller;

use Oro\Bundle\UserBundle\OroUserEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\SecurityBundle\Annotation\Acl;

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
        $this->dispatchGroupEvent(OroUserEvents::PRE_CREATE_GROUP);
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
        $this->dispatchGroupEvent(OroUserEvents::PRE_UPDATE_GROUP, $entity);
        return $this->update($entity);
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
     * @Template
     */
    public function indexAction(Request $request)
    {
        return array();
    }

    /**
     * Delete group
     *
     * @Route(
     *      "/delete/{id}",
     *      name="oro_user_group_delete",
     *      requirements={"id"="\d+"},
     *      methods="DELETE"
     * )
     * @Acl(
     *      id="oro_user_group_remove",
     *      type="entity",
     *      class="OroUserBundle:Group",
     *      permission="DELETE"
     * )
     */
    public function deleteAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $groupClass = $this->container->getParameter('oro_user.group.entity.class');
        $group = $em->getRepository($groupClass)->find($id);

        if (!$group) {
            throw $this->createNotFoundException(sprintf('Group with id %d could not be found.', $id));
        }

        $em->remove($group);
        $em->flush();

        return new JsonResponse('', 204);
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
            'form'     => $this->get('oro_user.form.group')->createView(),
        );
    }

    /**
     * @return EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        return $this->get('event_dispatcher');
    }

    /**
     * @param string $event
     * @param Group  $group
     */
    protected function dispatchGroupEvent($event, Group $group = null)
    {
        $this->getEventDispatcher()->dispatch($event, new GenericEvent($group));
    }
}

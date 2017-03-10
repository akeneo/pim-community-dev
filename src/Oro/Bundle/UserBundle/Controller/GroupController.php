<?php

namespace Oro\Bundle\UserBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\UserBundle\OroUserEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GroupController extends Controller
{
    /**
     * Create group form
     *
     * @Template("OroUserBundle:Group:update.html.twig")
     * @AclAncestor("pim_user_group_create")
     */
    public function createAction()
    {
        $this->dispatchGroupEvent(OroUserEvents::PRE_CREATE_GROUP);
        return $this->update(new Group());
    }

    /**
     * Edit group form
     *
     * @Template
     * @AclAncestor("pim_user_group_edit")
     */
    public function updateAction(Group $entity)
    {
        $this->dispatchGroupEvent(OroUserEvents::PRE_UPDATE_GROUP, $entity);
        return $this->update($entity);
    }

    /**
     * @AclAncestor("pim_user_group_index")
     * @Template
     */
    public function indexAction(Request $request)
    {
        return [];
    }

    /**
     * Delete group
     *
     * @AclAncestor("pim_user_group_remove")
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

            return new JsonResponse(
                [
                    'route' => 'oro_user_group_update',
                    'params' => ['id' => $entity->getId()]
                ]
            );
        }

        return [
            'form' => $this->get('oro_user.form.group')->createView(),
        ];
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

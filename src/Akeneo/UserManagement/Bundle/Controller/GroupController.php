<?php

namespace Akeneo\UserManagement\Bundle\Controller;

use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Remover\BaseRemover;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\UserEvents;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class GroupController extends Controller
{
    /**
     * Create group form
     *
     * @Template("PimUserBundle:Group:update.html.twig")
     * @AclAncestor("pim_user_group_create")
     */
    public function createAction()
    {
        $this->dispatchGroupEvent(UserEvents::PRE_CREATE_GROUP);
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
        $this->dispatchGroupEvent(UserEvents::PRE_UPDATE_GROUP, $entity);
        return $this->update($entity);
    }

    /**
     * Delete group
     *
     * @AclAncestor("pim_user_group_remove")
     */
    public function deleteAction(Request $request, $id)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $groupClass = $this->container->getParameter('pim_user.entity.group.class');
        $group = $em->getRepository($groupClass)->find($id);

        if (!$group) {
            throw $this->createNotFoundException(sprintf('Group with id %d could not be found.', $id));
        }

        $remover = $this->get('pim_user.remover.user_group');
        $remover->remove($group);

        return new JsonResponse('', 204);
    }

    /**
     * @param Group $entity
     *
     * @return array|JsonResponse
     */
    private function update(Group $entity)
    {
        if ($this->get('pim_user.form.handler.group')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('pim_user.controller.group.message.saved')
            );

            return new JsonResponse(
                [
                    'route' => 'pim_user_group_update',
                    'params' => ['id' => $entity->getId()]
                ]
            );
        }

        return [
            'form' => $this->get('pim_user.form.group')->createView(),
        ];
    }

    /**
     * @param string $event
     * @param Group  $group
     */
    private function dispatchGroupEvent($event, Group $group = null)
    {
        $this->get('event_dispatcher')->dispatch($event, new GenericEvent($group));
    }
}

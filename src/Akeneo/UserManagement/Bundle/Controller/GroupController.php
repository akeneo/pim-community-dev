<?php

namespace Akeneo\UserManagement\Bundle\Controller;

use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\UserManagement\Bundle\Form\Handler\GroupHandler;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\UserEvents;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class GroupController extends Controller
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var RemoverInterface */
    private $remover;

    /** @var GroupHandler */
    private $groupHandler;

    /** @var TranslatorInterface */
    private $translator;

    /** @var FormInterface */
    private $form;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        RemoverInterface $remover,
        GroupHandler $groupHandler,
        TranslatorInterface $translator,
        FormInterface $form,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->remover = $remover;
        $this->groupHandler = $groupHandler;
        $this->translator = $translator;
        $this->form = $form;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Create group form
     *
     * @Template("PimUserBundle:Group:update.html.twig")
     * @AclAncestor("pim_user_group_create")
     */
    public function create()
    {
        $this->dispatchGroupEvent(UserEvents::PRE_CREATE_GROUP);

        return $this->update(new Group());
    }

    /**
     * Edit group form
     *
     * @Template("PimUserBundle:Group:update.html.twig")
     * @AclAncestor("pim_user_group_edit")
     */
    public function update(Group $entity)
    {
        $this->dispatchGroupEvent(UserEvents::PRE_UPDATE_GROUP, $entity);

        return $this->updateGroup($entity);
    }

    /**
     * Delete group
     *
     * @AclAncestor("pim_user_group_remove")
     */
    public function delete(Request $request, $id)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $groupClass = $this->container->getParameter('pim_user.entity.group.class');
        $group = $this->entityManager->getRepository($groupClass)->find($id);

        if (!$group) {
            throw $this->createNotFoundException(sprintf('Group with id %d could not be found.', $id));
        }

        try {
            $this->remover->remove($group);
        } catch (\Exception $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], 500);
        };

        return new JsonResponse('', 204);
    }

    /**
     * @param Group $entity
     *
     * @return array|JsonResponse
     */
    private function updateGroup(Group $entity)
    {
        if ($this->groupHandler->process($entity)) {
            $this->addFlash(
                'success',
                $this->translator->trans('pim_user.controller.group.message.saved')
            );

            return new JsonResponse(
                [
                    'route' => 'pim_user_group_update',
                    'params' => ['id' => $entity->getId()]
                ]
            );
        }

        return [
            'form' => $this->form->createView(),
        ];
    }

    /**
     * @param string $event
     * @param Group  $group
     */
    private function dispatchGroupEvent($event, Group $group = null)
    {
        $this->eventDispatcher->dispatch($event, new GenericEvent($group));
    }
}

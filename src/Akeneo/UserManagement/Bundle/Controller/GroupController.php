<?php

namespace Akeneo\UserManagement\Bundle\Controller;

use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\UserManagement\Bundle\Form\Handler\GroupHandler;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Akeneo\UserManagement\Component\UserEvents;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class GroupController extends AbstractController
{
    public function __construct(
        private readonly GroupRepositoryInterface $groupRepository,
        private readonly RemoverInterface $remover,
        private readonly GroupHandler $groupHandler,
        private readonly TranslatorInterface $translator,
        private readonly FormInterface $form,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * Create group form
     *
     * @AclAncestor("pim_user_group_create")
     */
    public function create(): Response
    {
        $this->dispatchGroupEvent(UserEvents::PRE_CREATE_GROUP);

        $newGroup = new Group();
        return $this->updateGroup($newGroup);
    }

    /**
     * Edit group form
     *
     * @AclAncestor("pim_user_group_edit")
     */
    public function update(int $id): Response
    {
        $group = $this->groupRepository->find($id);

        $this->dispatchGroupEvent(UserEvents::PRE_UPDATE_GROUP, $group);

        return $this->updateGroup($group);
    }

    /**
     * Delete group
     *
     * @AclAncestor("pim_user_group_remove")
     */
    public function delete(Request $request, int $id): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $group = $this->groupRepository->find($id);

        if (null === $group) {
            throw $this->createNotFoundException(sprintf('Group with id %d could not be found.', $id));
        }

        try {
            $this->remover->remove($group);
        } catch (\Exception $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        };

        return new JsonResponse('', 204);
    }

    private function updateGroup(Group $group): Response
    {
        if ($this->groupHandler->process($group)) {
            $this->addFlash(
                'success',
                $this->translator->trans('pim_user.controller.group.message.saved')
            );

            return new JsonResponse(
                [
                    'route' => 'pim_user_group_update',
                    'params' => ['id' => $group->getId()]
                ]
            );
        }

        return $this->render('@PimUser/Group/update.html.twig', [
            'form' => $this->form->createView(),
        ]);
    }

    private function dispatchGroupEvent(string $event, ?Group $group = null): void
    {
        $this->eventDispatcher->dispatch(new GenericEvent($group), $event);
    }
}

<?php

namespace Akeneo\UserManagement\Bundle\Controller;

use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\UserManagement\Bundle\Form\Handler\AclRoleHandler;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclSidManager;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class RoleController extends AbstractController
{
    public function __construct(
        private readonly RoleRepositoryInterface $roleRepository,
        private readonly RemoverInterface $remover,
        private readonly AclSidManager $aclSidManager,
        private readonly AclRoleHandler $aclRoleHandler,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @AclAncestor("pim_user_role_create")
     */
    public function create(): Response
    {
        $newRole = new Role();
        return $this->updateRole($newRole);
    }

    /**
     * @AclAncestor("pim_user_role_edit")
     */
    public function update(int $id): Response
    {
        $role = $this->roleRepository->find($id);
        return $this->updateRole($role);
    }

    /**
     * Delete role
     *
     * @AclAncestor("pim_user_role_remove")
     */
    public function delete(Request $request, $id): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $role = $this->roleRepository->find($id);

        if (null === $role) {
            throw $this->createNotFoundException(sprintf('Role with id %d could not be found.', $id));
        }

        try {
            $this->remover->remove($role);

            if ($this->aclSidManager->isAclEnabled()) {
                $this->aclSidManager->deleteSid($this->aclSidManager->getSid($role));
            }
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse('', 204);
    }

    private function updateRole(Role $role): Response
    {
        $this->aclRoleHandler->createForm($role);

        if ($this->aclRoleHandler->process($role)) {
            $this->addFlash(
                'success',
                $this->translator->trans('pim_user.controller.role.message.saved')
            );

            return new JsonResponse(
                ['route' => 'pim_user_role_update', 'params' => ['id' => $role->getId()]]
            );
        }

        return $this->render('@PimUser/Role/update.html.twig', [
            'form' => $this->aclRoleHandler->createView(),
            'privilegesConfig' => $this->container->getParameter('pim_user.privileges'),
        ]);
    }
}

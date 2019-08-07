<?php

namespace Akeneo\UserManagement\Bundle\Controller;

use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\UserManagement\Bundle\Form\Handler\AclRoleHandler;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclSidManager;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class RoleController extends Controller
{
    /** @var RoleRepositoryInterface */
    private $roleRepository;

    /** @var RemoverInterface */
    private $remover;

    /** @var AclSidManager */
    private $aclSidManager;

    /** @var AclRoleHandler */
    private $aclRoleHandler;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        RoleRepositoryInterface $roleRepository,
        RemoverInterface $remover,
        AclSidManager $aclSidManager,
        AclRoleHandler $aclRoleHandler,
        TranslatorInterface $translator
    ) {
        $this->roleRepository = $roleRepository;
        $this->remover = $remover;
        $this->aclSidManager = $aclSidManager;
        $this->aclRoleHandler = $aclRoleHandler;
        $this->translator = $translator;
    }

    /**
     * @AclAncestor("pim_user_role_create")
     * @Template("PimUserBundle:Role:update.html.twig")
     */
    public function create()
    {
        return $this->update(new Role());
    }

    /**
     * @AclAncestor("pim_user_role_edit")
     * @Template("PimUserBundle:Role:update.html.twig")
     */
    public function update(Role $entity)
    {
        return $this->updateUser($entity);
    }

    /**
     * Delete role
     *
     * @AclAncestor("pim_user_role_remove")
     */
    public function delete(Request $request, $id)
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

    /**
     * @param Role $entity
     *
     * @return array|JsonResponse
     */
    private function updateUser(Role $entity)
    {
        $this->aclRoleHandler->createForm($entity);

        if ($this->aclRoleHandler->process($entity)) {
            $this->addFlash(
                'success',
                $this->translator->trans('pim_user.controller.role.message.saved')
            );

            return new JsonResponse(
                ['route' => 'pim_user_role_update', 'params' => ['id' => $entity->getId()]]
            );
        }

        return [
            'form' => $this->aclRoleHandler->createView(),
            'privilegesConfig' => $this->container->getParameter('pim_user.privileges'),
        ];
    }
}

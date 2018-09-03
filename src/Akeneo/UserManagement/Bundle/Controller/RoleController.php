<?php

namespace Akeneo\UserManagement\Bundle\Controller;

use Akeneo\UserManagement\Component\Model\Role;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class RoleController extends Controller
{
    /**
     * @AclAncestor("pim_user_role_create")
     * @Template("PimUserBundle:Role:update.html.twig")
     */
    public function createAction()
    {
        return $this->update(new Role());
    }

    /**
     * @AclAncestor("pim_user_role_edit")
     * @Template
     */
    public function updateAction(Role $entity)
    {
        return $this->update($entity);
    }

    /**
     * Delete role
     *
     * @AclAncestor("pim_user_role_remove")
     */
    public function deleteAction(Request $request, $id)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $roleClass = $this->container->getParameter('pim_user.entity.role.class');
        $role = $em->getRepository($roleClass)->find($id);

        if (!$role) {
            throw $this->createNotFoundException(sprintf('Role with id %d could not be found.', $id));
        }

        try {
            $this->container->get('pim_user.remover.role')->remove($role);

            $aclSidManager = $this->get('oro_security.acl.sid_manager');
            if ($aclSidManager->isAclEnabled()) {
                $aclSidManager->deleteSid($aclSidManager->getSid($role));
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
    private function update(Role $entity)
    {
        $aclRoleHandler = $this->get('pim_user.form.handler.acl_role');
        $aclRoleHandler->createForm($entity);

        if ($aclRoleHandler->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('pim_user.controller.role.message.saved')
            );

            return new JsonResponse(
                ['route' => 'pim_user_role_update', 'params' => ['id' => $entity->getId()]]
            );
        }

        return [
            'form'             => $aclRoleHandler->createView(),
            'privilegesConfig' => $this->container->getParameter('pim_user.privileges'),
        ];
    }
}

<?php

namespace Oro\Bundle\UserBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\UserBundle\Entity\Role;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RoleController extends Controller
{
    /**
     * @AclAncestor("pim_user_role_create")
     * @Template("OroUserBundle:Role:update.html.twig")
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
     * @AclAncestor("pim_user_role_index")
     * @Template
     */
    public function indexAction(Request $request)
    {
        return [];
    }

    /**
     * Delete role
     *
     * @AclAncestor("pim_user_role_remove")
     */
    public function deleteAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $roleClass = $this->container->getParameter('oro_user.role.entity.class');
        $role = $em->getRepository($roleClass)->find($id);

        if (!$role) {
            throw $this->createNotFoundException(sprintf('Role with id %d could not be found.', $id));
        }

        $em->remove($role);

        $aclSidManager = $this->get('oro_security.acl.sid_manager');
        if ($aclSidManager->isAclEnabled()) {
            $aclSidManager->deleteSid($aclSidManager->getSid($role));
        }

        $em->flush();

        return new JsonResponse('', 204);
    }

    /**
     * @param Role $entity
     * @return array
     */
    protected function update(Role $entity)
    {
        $aclRoleHandler = $this->get('oro_user.form.handler.acl_role');
        $aclRoleHandler->createForm($entity);

        if ($aclRoleHandler->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('oro.user.controller.role.message.saved')
            );

            return new JsonResponse(
                ['route' => 'oro_user_role_update', 'params' => ['id' => $entity->getId()]]
            );
        }

        return [
            'form'             => $aclRoleHandler->createView(),
            'privilegesConfig' => $this->container->getParameter('oro_user.privileges'),
        ];
    }
}

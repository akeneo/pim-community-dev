<?php

namespace Pim\Bundle\UserBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\UserBundle\Entity\Role;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RoleController
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RoleController extends Controller
{
    /**
     * @AclAncestor("pim_user_role_create")
     * @Template("PimUserBundle:Role:update.html.twig")
     */
    public function createAction(Request $request)
    {
        $form = $this->createForm('pim_user_role_form', new Role());

        if ($request->isMethod('POST') || $request->isMethod('PUT')) {
            $form->submit($request);

            if ($form->isValid()) {
                $role = $form->getData();
                $this->get('pim_user.saver.role')->save($role);

                $privileges = new ArrayCollection();
                foreach ($form as $field) {
                    if ($field->getParent()->getName() === 'oro_acl_collection') {
                        $privileges->add($field->getData());
                    }
                }

                $aclManager = $this->get('oro_security.acl.manager');
                $aclManager->getPrivilegeRepository()->savePrivileges($aclManager->getSid($role), $privileges);

                $message = $this->get('translator')->trans('oro.user.controller.role.message.saved');
                $this->get('session')->getFlashBag()->add('success', $message);

                return $this->get('oro_ui.router')->actionRedirect(
                    ['route' => 'pim_user_role_update', 'parameters' => ['id' => $role->getId()]],
                    ['route' => 'pim_user_role_index']
                );
            }
        }

        return [
            'form'             => $form->createView(),
            'privilegesConfig' => $this->container->getParameter('pim_user.privileges'),
        ];
    }

    /**
     * @AclAncestor("pim_user_role_edit")
     * @Template
     */
    public function updateAction(Request $request, Role $role)
    {
        $form = $this->createForm('pim_user_role_form', $role);

        if ($request->isMethod('POST') || $request->isMethod('PUT')) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->get('pim_user.saver.role')->save($role);

                $privileges = new ArrayCollection();
                foreach ($form as $field) {
                    if ($field->getParent()->getName() === 'oro_acl_collection') {
                        $privileges->add($field->getData());
                    }
                }

                $aclManager = $this->get('oro_security.acl.manager');
                $aclManager->getPrivilegeRepository()->savePrivileges($aclManager->getSid($role), $privileges);

                $message = $this->get('translator')->trans('oro.user.controller.role.message.saved');
                $this->get('session')->getFlashBag()->add('success', $message);

                return $this->get('oro_ui.router')->actionRedirect(
                    ['route' => 'pim_user_role_update', 'parameters' => ['id' => $role->getId()]],
                    ['route' => 'pim_user_role_index']
                );
            }
        }

        return [
            'form'             => $form->createView(),
            'privilegesConfig' => $this->container->getParameter('pim_user.privileges'),
        ];
    }

    /**
     * @AclAncestor("pim_user_role_index")
     * @Template
     */
    public function indexAction()
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
        $role = $this->get('pim_user.repository.role')->find($id);

        if (!$role) {
            throw $this->createNotFoundException(sprintf('Role with id %d could not be found.', $id));
        }

        $this->get('pim_user.remover.role')->remove($role);

        return new JsonResponse('', 204);
    }
}

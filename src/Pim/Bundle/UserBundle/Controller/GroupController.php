<?php

namespace Pim\Bundle\UserBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\UserBundle\Entity\Group;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GroupController
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupController extends Controller
{
    /**
     * Create group form
     *
     * @Template("PimUserBundle:Group:update.html.twig")
     * @AclAncestor("pim_user_group_create")
     */
    public function createAction(Request $request)
    {
        $form = $this->createForm('pim_user_group');

        if ($request->isMethod('POST')) {
            $form->submit($request);

            if ($form->isValid()) {
                $group = $form->getData();
                $this->get('pim_user.saver.group')->save($group);

                $message = $this->get('translator')->trans('oro.user.controller.group.message.saved');
                $this->get('session')->getFlashBag()->add('success', $message);

                return $this->get('oro_ui.router')->actionRedirect(
                    ['route' => 'pim_user_group_update', 'parameters' => ['id' => $group->getId()]],
                    ['route' => 'pim_user_group_index']
                );
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Edit group form
     *
     * @Template
     * @AclAncestor("pim_user_group_edit")
     */
    public function updateAction(Request $request, Group $group)
    {
        $form = $this->createForm('pim_user_group', $group);

        if ($request->isMethod('POST')) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->get('pim_user.saver.group')->save($group);

                $message = $this->get('translator')->trans('oro.user.controller.group.message.saved');
                $this->get('session')->getFlashBag()->add('success', $message);

                return $this->get('oro_ui.router')->actionRedirect(
                    ['route' => 'pim_user_group_update', 'parameters' => ['id' => $group->getId()]],
                    ['route' => 'pim_user_group_index']
                );
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @AclAncestor("pim_user_group_index")
     * @Template
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @AclAncestor("pim_user_group_remove")
     */
    public function deleteAction(Group $group)
    {
        $this->get('pim_user.remover.group')->remove($group);

        return new JsonResponse('', 204);
    }
}

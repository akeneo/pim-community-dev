<?php

namespace Pim\Bundle\UserBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\UserBundle\Entity\User;
use Pim\Bundle\UserBundle\Entity\UserApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class UserController
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserController extends Controller
{
    /**
     * @Template
     * @AclAncestor("pim_user_user_index")
     */
    public function viewAction($id)
    {
        $user = $this->get('pim_user.repository.user')->find($id);

        if (null === $user) {
            throw new NotFoundHttpException();
        }

        return [
            'entity'  => $user,
            'dynamic' => []
        ];
    }

    /**
     * @Template("PimUserBundle:User:view.html.twig")
     */
    public function viewProfileAction()
    {
        return [
            'entity'    => $this->getUser(),
            'dynamic'   => [],
            'editRoute' => 'pim_user_profile_update'
        ];
    }

    /**
     * @Template("PimUserBundle:User:update.html.twig")
     */
    public function updateProfileAction(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm('pim_user_user', $user);

        if ($request->isMethod('POST') || $request->isMethod('PUT')) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->get('pim_user.saver.user')->save($user);

                $message = $this->get('translator')->trans('oro.user.controller.user.message.saved');
                $this->get('session')->getFlashBag()->add('success', $message);

                return $this->get('oro_ui.router')->actionRedirect(
                    ['route' => 'pim_user_profile_update'],
                    ['route' => 'pim_user_profile_view']
                );
            }
        }

        return [
            'form'      => $form->createView(),
            'editRoute' => 'pim_user_profile_update'
        ];
    }

    /**
     * @AclAncestor("pim_user_user_edit")
     */
    public function apigenAction(User $user)
    {
        if (!$api = $user->getApi()) {
            $api = new UserApi();
        }

        $api->setApiKey($api->generateKey())
            ->setUser($user);

        $em = $this->getDoctrine()->getManager();

        $em->persist($api);
        $em->flush();

        return $this->getRequest()->isXmlHttpRequest()
            ? new JsonResponse($api->getApiKey())
            : $this->forward('PimUserBundle:User:view', ['user' => $user]);
    }

    /**
     * @Template("PimUserBundle:User:update.html.twig")
     * @AclAncestor("pim_user_user_create")
     */
    public function createAction(Request $request)
    {
        $form = $this->createForm('pim_user_user');

        if ($request->isMethod('POST') || $request->isMethod('PUT')) {
            $form->submit($request);

            if ($form->isValid()) {
                $user = $form->getData();
                $this->get('pim_user.saver.user')->save($user);

                $message = $this->get('translator')->trans('oro.user.controller.user.message.saved');
                $this->get('session')->getFlashBag()->add('success', $message);

                return $this->get('oro_ui.router')->actionRedirect(
                    ['route' => 'pim_user_update', 'parameters' => ['id' => $user->getId()]],
                    ['route' => 'pim_user_view', 'parameters' => ['id'   => $user->getId()]]
                );
            }
        }

        return [
            'form'      => $form->createView(),
            'editRoute' => ''
        ];
    }

    /**
     * @Template
     * @AclAncestor("pim_user_user_edit")
     */
    public function updateAction(Request $request, $id)
    {
        $user = $this->get('pim_user.repository.user')->find($id);

        if (null === $user) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm('pim_user_user', $user);

        if ($request->isMethod('POST') || $request->isMethod('PUT')) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->get('pim_user.saver.user')->save($user);

                $message = $this->get('translator')->trans('oro.user.controller.user.message.saved');
                $this->get('session')->getFlashBag()->add('success', $message);

                return $this->get('oro_ui.router')->actionRedirect(
                    ['route' => 'pim_user_update', 'parameters' => ['id' => $user->getId()]],
                    ['route' => 'pim_user_view', 'parameters' => ['id'   => $user->getId()]]
                );
            }
        }

        return [
            'form'      => $form->createView(),
            'editRoute' => ''
        ];
    }

    /**
     * @Template
     * @AclAncestor("pim_user_user_index")
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @AclAncestor("pim_user_user_remove")
     */
    public function deleteAction($id)
    {
        $currentUser = $this->getUser();

        if (is_object($currentUser) && $currentUser->getId() == $id) {
            return new JsonResponse('', 403);
        }

        $user = $this->get('pim_user.repository.user')->find($id);

        if (!$user) {
            throw $this->createNotFoundException(sprintf('User with id %d could not be found.', $id));
        }

        $this->get('pim_user.remover.user')->remove($user);

        return new JsonResponse('', 204);
    }
}

<?php

namespace Oro\Bundle\UserBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\UserBundle\Entity\User;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Bundle\UserBundle\Event\UserEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
{
    /**
     * @Template
     *
     * @AclAncestor("pim_user_user_index")
     */
    public function viewAction($id)
    {
        $user = $this->get('pim_user.repository.user')->find($id);

        return $this->view($user);
    }

    /**
     * @Template("OroUserBundle:User:view.html.twig")
     */
    public function viewProfileAction()
    {
        return $this->view($this->getUser(), 'oro_user_profile_update');
    }

    /**
     * @Template("OroUserBundle:User:update.html.twig")
     */
    public function updateProfileAction()
    {
        $user = $this->getUser();
        $route = $this->get('router')->generate('oro_user_profile_update');

        if ($this->get('oro_user.form.handler.user')->process($user)) {
            $this->update($user);

            return new RedirectResponse($route);
        }

        return [
            'form'      => $this->get('oro_user.form.user')->createView(),
            'editRoute' => $route,
        ];
    }

    /**
     * Create user form
     *
     * @Template("OroUserBundle:User:update.html.twig")
     * @AclAncestor("pim_user_user_create")
     */
    public function createAction()
    {
        $user = $this->get('pim_user.factory.user')->create();

        if ($this->get('oro_user.form.handler.user')->process($user)) {
            $user = $this->update($user);

            return new RedirectResponse(
                $this->get('router')->generate('oro_user_update', ['id' => $user->getId()])
            );
        }

        return [
            'form'      => $this->get('oro_user.form.user')->createView(),
            'editRoute' => $this->get('router')->generate('oro_user_create')
        ];
    }

    /**
     * Edit user form
     *
     * @Template
     * @AclAncestor("pim_user_user_edit")
     */
    public function updateAction($id)
    {
        $user = $this->get('pim_user.repository.user')->find($id);
        if (null === $user) {
            throw new NotFoundHttpException(sprintf('User with the ID "%s" does not exit', $id));
        }

        $route = $this->get('router')->generate('oro_user_update', ['id' => $user->getId()]);

        if ($this->get('oro_user.form.handler.user')->process($user)) {
            $this->update($user);

            return new RedirectResponse($route);
        }

        return [
            'form'      => $this->get('oro_user.form.user')->createView(),
            'editRoute' => $route
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
     * Delete user
     *
     * @AclAncestor("pim_user_user_remove")
     */
    public function deleteAction(Request $request, $id)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $tokenStorage = $this->get('security.token_storage')->getToken();
        $currentUser = $tokenStorage ? $tokenStorage->getUser() : null;
        if (is_object($currentUser) && $currentUser->getId() != $id) {
            $em = $this->get('doctrine.orm.entity_manager');
            $userClass = $this->container->getParameter('oro_user.entity.class');
            $user = $em->getRepository($userClass)->find($id);

            if (!$user) {
                throw $this->createNotFoundException(sprintf('User with id %d could not be found.', $id));
            }

            $em->remove($user);
            $em->flush();

            return new JsonResponse('', 204);
        } else {
            return new JsonResponse('', 403);
        }
    }

    /**
     * @param UserInterface $user
     *
     * @return UserInterface
     */
    protected function update(UserInterface $user)
    {
        $this->get('event_dispatcher')->dispatch(
            UserEvent::POST_UPDATE,
            new GenericEvent($user, ['current_user' => $this->getUser()])
        );

        $this->get('session')->getFlashBag()->add(
            'success',
            $this->get('translator')->trans('oro.user.controller.user.message.saved')
        );

        $this->get('session')->remove('dataLocale');

        return $user;
    }

    /**
     * @param User   $user
     * @param string $editRoute
     *
     * @return array
     */
    protected function view(User $user, $editRoute = '')
    {
        $output = [
            'entity'   => $user,
            'dynamic'  => []
        ];

        if ($editRoute) {
            $output = array_merge($output, ['editRoute' => $editRoute]);
        }

        return $output;
    }
}

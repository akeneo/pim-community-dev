<?php

namespace Oro\Bundle\UserBundle\Controller;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\PersistentCollection;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\UserBundle\Autocomplete\UserSearchHandler;
use Oro\Bundle\UserBundle\Entity\UserApi;
use Pim\Bundle\UserBundle\Entity\User;
use Pim\Bundle\UserBundle\Event\UserEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class UserController extends Controller
{
    /**
     * @Template
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
        return $this->update(
            $this->getUser(),
            'oro_user_profile_update',
            ['route' => 'oro_user_profile_view']
        );
    }

    /**
     * @AclAncestor("pim_user_user_edit")
     *
     * @param int $id
     *
     * @return JsonResponse|Response
     */
    public function apigenAction($id)
    {
        $userRepository = $this->container->get('pim_user.repository.user');
        $user = $userRepository->findOneBy(['id' => $id]);

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
            : $this->forward('OroUserBundle:User:view', ['user' => $user]);
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

        return $this->update($user);
    }

    /**
     * Edit user form
     *
     * @Template
     * @AclAncestor("pim_user_user_edit")
     */
    public function updateAction($id)
    {
        return $this->update($id);
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
     * @param mixed  $user
     * @param string $updateRoute
     * @param array  $viewRoute
     *
     * @return array
     */
    protected function update($user, $updateRoute = '', $viewRoute = [])
    {
        if (!$user instanceof UserInterface) {
            $user = $this->get('pim_user.repository.user')->find($user);
        }

        if ($this->get('oro_user.form.handler.user')->process($user)) {
            $this->get('event_dispatcher')->dispatch(
                UserEvent::POST_UPDATE,
                new GenericEvent($user, ['current_user' => $this->getUser()])
            );

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('oro.user.controller.user.message.saved')
            );

            if (count($viewRoute)) {
                $closeButtonRoute = $viewRoute;
            } else {
                $closeButtonRoute = [
                    'route'      => 'oro_user_view',
                    'parameters' => ['id' => $user->getId()]
                ];
            }

            $response = $this->get('oro_ui.router')->actionRedirect(
                [
                    'route'      => 'oro_user_update',
                    'parameters' => ['id' => $user->getId()],
                ],
                $closeButtonRoute
            );

            $response->headers->set('oroFullRedirect', true);

            return $response;
        }

        return [
            'form'      => $this->get('oro_user.form.user')->createView(),
            'editRoute' => $updateRoute
        ];
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

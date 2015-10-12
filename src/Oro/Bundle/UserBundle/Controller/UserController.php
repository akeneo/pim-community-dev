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
            array('route' => 'oro_user_profile_view')
        );
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
            : $this->forward('OroUserBundle:User:view', array('user' => $user));
    }

    /**
     * Create user form
     *
     * @Template("OroUserBundle:User:update.html.twig")
     * @AclAncestor("pim_user_user_create")
     */
    public function createAction()
    {
        $user = $this->get('oro_user.manager')->createUser();

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
        return array();
    }

    /**
     * Delete user
     *
     * @AclAncestor("pim_user_user_remove")
     */
    public function deleteAction($id)
    {
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
            if ($user === $this->getUser()) {
                $this->get('translator')->setLocale($user->getUiLocale()->getCode());
            }
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('oro.user.controller.user.message.saved')
            );

            if (count($viewRoute)) {
                $closeButtonRoute = $viewRoute;
            } else {
                $closeButtonRoute = array(
                    'route'      => 'oro_user_view',
                    'parameters' => array('id' => $user->getId())
                );
            }

            $this->get('event_dispatcher')->dispatch(
                UserEvent::POST_UPDATE,
                new GenericEvent($user, ['user' => $this->getUser()])
            );

            return $this->get('oro_ui.router')->actionRedirect(
                array(
                    'route'      => 'oro_user_update',
                    'parameters' => array('id' => $user->getId()),
                ),
                $closeButtonRoute
            );
        }

        return array(
            'form'      => $this->get('oro_user.form.user')->createView(),
            'editRoute' => $updateRoute
        );
    }

    /**
     * @param User   $user
     * @param string $editRoute
     *
     * @return array
     */
    protected function view(User $user, $editRoute = '')
    {
        $output = array(
            'entity'   => $user,
            'dynamic'  => []
        );

        if ($editRoute) {
            $output = array_merge($output, array('editRoute' => $editRoute));
        }

        return $output;
    }
}

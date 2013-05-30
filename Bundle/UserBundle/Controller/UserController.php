<?php

namespace Oro\Bundle\UserBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserApi;
use Oro\Bundle\UserBundle\Entity\UserManager;

/**
 * @Acl(
 *      id="oro_user_user",
 *      name="User manipulation",
 *      description="User manipulation",
 *      parent="oro_user"
 * )
 */
class UserController extends Controller
{
    /**
     * @Route("/view/{id}", name="oro_user_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_user_user_view",
     *      name="View user user",
     *      description="View user user",
     *      parent="oro_user_user"
     * )
     */
    public function viewAction(User $user)
    {
        return array(
            'user' => $user,
        );
    }

    /**
     * @Route("/apigen/{id}", name="oro_user_apigen", requirements={"id"="\d+"})
     * @Acl(
     *      id="oro_user_user_apigen",
     *      name="Generate new API key",
     *      description="Generate new API key",
     *      parent="oro_user_user"
     * )
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
     * @Route("/create", name="oro_user_create")
     * @Template("OroUserBundle:User:update.html.twig")
     * @Acl(
     *      id="oro_user_user_create",
     *      name="Create user",
     *      description="Create user",
     *      parent="oro_user_user"
     * )
     */
    public function createAction()
    {
        $user = $this->get('oro_user.manager')->createFlexible();

        return $this->updateAction($user);
    }

    /**
     * Edit user form
     *
     * @Route("/update/{id}", name="oro_user_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="oro_user_user_update",
     *      name="Edit user",
     *      description="Edit user",
     *      parent="oro_user_user"
     * )
     */
    public function updateAction(User $entity)
    {
        if ($this->get('oro_user.form.handler.user')->process($entity)) {
            $this->get('session')->getFlashBag()->add('success', 'User successfully saved');

            return $this->redirect($this->generateUrl('oro_user_index'));
        }

        return array(
            'form' => $this->get('oro_user.form.user')->createView(),
        );
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_user_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id="oro_user_user_list",
     *      name="View list of users",
     *      description="View list of users",
     *      parent="oro_user_user"
     * )
     */
    public function indexAction()
    {
        $view = $this->get('oro_user.user_datagrid_manager')->getDatagrid()->createView();

        return 'json' == $this->getRequest()->getRequestFormat()
            ? $this->get('oro_grid.renderer')->renderResultsJsonResponse($view)
            : $this->render('OroUserBundle:User:index.html.twig', array('datagrid' => $view));
    }
}

<?php

namespace Oro\Bundle\UserBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserApi;

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

    /**
     * @Route("/autocomplete", name="oro_user_autocomplete")
     * @Acl(
     *      id="oro_user_user_autocomplete",
     *      name="Autocomplete list of users",
     *      description="Autocomplete list of users",
     *      parent="oro_user_user"
     * )
     */
    public function autocompleteAction()
    {
        $query = $this->getRequest()->get('q');
        $page = (int)$this->getRequest()->get('page', 1);

        $data = array(
            'results' => array(
                array('id' => 1, 'username' => 'admin', 'first_name' => 'Test', 'last_name' => 'User', 'email' => 'admin@test.com', 'avatar' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAcCAYAAAB75n/uAAAC70lEQVR42u2V3UtTYRzHu+mFwCwK+gO6CEryPlg7yiYx50vDqUwjFIZDSYUk2ZTmCysHvg9ZVggOQZiRScsR4VwXTjEwdKZWk8o6gd5UOt0mbev7g/PAkLONIOkiBx+25/v89vuc85zn2Q5Fo9F95UDwnwhS5HK5TyqVRv8m1JN6k+AiC+fn54cwbgFNIrTQ/J9IqDcJJDGBHsgDgYBSq9W6ysvLPf39/SSUUU7zsQ1yc3MjmN90OBzfRkZG1umzQqGIxPSTkIBjgdDkaGNjoza2kcFgUCE/QvMsq6io2PV6vQu1tbV8Xl7etkql2qqvr/+MbDE/Pz8s9OP2Cjhwwmw29+4R3Kec1WZnZ4fn5uamc3Jyttra2qbH8ero6JgdHh5+CvFHq9X6JZHgzODgoCVW0NPTY0N+ltU2Nzdv4GqXsYSrPp+vDw80aLFYxru6uhyQ/rDb7a8TCVJDodB1jUazTVlxcXGQ5/mbyE+z2u7u7veY38BVT3Z2djopm5qa6isrK/tQWVn5qb29fSGR4DC4PDAwMEsZHuArjGnyGKutq6v7ajQaF6urq9/MzMz0QuSemJiwQDwGkR0POhhXgILjNTU1TaWlpTxlOp1uyWQyaUjMajMzM8Nut/tJQUHBOpZppbCwkM/KytrBznuL9xDVxBMo8KXHYnu6qKjIivmrbIy67x6Px4Yd58W672ApfzY0NCyNjo7OZmRkiAv8fr+O47iwmABXtoXaG3uykF6vX7bZbF6cgZWqqiqezYkKcNtmjO+CF2AyhufgjsvlMiU7vXEF+4C4ALf9CwdrlVAqlcFkTdRqdQSHLUDgBEeSCrArAsiGwENs0XfJBE6ncxm1D8Aj/B6tigkkJSUlmxSwLYhMDeRsyyUCd+lHrWxtbe2aTCbbZTn1ZD92F0Cr8GBfgnsgDZwDt8EzMBmHMXBLqD0PDMAh9Gql3iRIESQSIAXp4CRIBZeEjIvDFZAm1J4C6UK9ROiZcvCn/+8FvwHtDdJEaRY+oQAAAABJRU5ErkJggg==')
            ),
            'more' => false
        );
        return new JsonResponse($data);
    }
}

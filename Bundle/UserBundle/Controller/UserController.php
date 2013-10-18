<?php

namespace Oro\Bundle\UserBundle\Controller;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Autocomplete\UserSearchHandler;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserApi;

use Oro\Bundle\OrganizationBundle\Entity\Manager\BusinessUnitManager;
use Oro\Bundle\UserBundle\Datagrid\UserEmailDatagridManager;

class UserController extends Controller
{
    /**
     * @Route("/view/{id}", name="oro_user_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_user_user_view",
     *      type="entity",
     *      class="OroUserBundle:User",
     *      permission="VIEW"
     * )
     */
    public function viewAction(User $user)
    {
        return $this->view($user);
    }

    /**
     * @Route("/profile/view", name="oro_user_profile_view")
     * @Template("OroUserBundle:User:view.html.twig")
     */
    public function viewProfileAction()
    {
        return $this->view($this->getUser(), 'oro_user_profile_update');
    }

    /**
     * @Route("/profile/edit", name="oro_user_profile_update")
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
     * @Route("/apigen/{id}", name="oro_user_apigen", requirements={"id"="\d+"})
     * @AclAncestor("oro_user_user_update")
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
     *      type="entity",
     *      class="OroUserBundle:User",
     *      permission="CREATE"
     * )
     */
    public function createAction()
    {
        $user = $this->get('oro_user.manager')->createFlexible();

        return $this->update($user);
    }

    /**
     * Edit user form
     *
     * @Route("/update/{id}", name="oro_user_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="oro_user_user_update",
     *      type="entity",
     *      class="OroUserBundle:User",
     *      permission="EDIT"
     * )
     */
    public function updateAction(User $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_user_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @AclAncestor("oro_user_user_view")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @param User $entity
     * @param string $updateRoute
     * @param array $viewRoute
     * @return array
     */
    protected function update(User $entity, $updateRoute = '', $viewRoute = array())
    {
        if ($this->get('oro_user.form.handler.user')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('oro.user.controller.user.message.saved')
            );

            if (count($viewRoute)) {
                $closeButtonRoute = $viewRoute;
            } else {
                $closeButtonRoute = array(
                    'route' => 'oro_user_view',
                    'parameters' => array('id' => $entity->getId())
                );
            }
            return $this->get('oro_ui.router')->actionRedirect(
                array(
                    'route' => 'oro_user_update',
                    'parameters' => array('id' => $entity->getId()),
                ),
                $closeButtonRoute
            );
        }

        return array(
            'form' => $this->get('oro_user.form.user')->createView(),
            'businessUnits' => $this->getBusinessUnitManager()->getBusinessUnitsTree($entity),
            'editRoute' => $updateRoute
        );
    }

    /**
     * @param User $user
     * @param string $editRoute
     * @return array
     */
    protected function view(User $user, $editRoute = '')
    {
        if (array_key_exists(
            'refresh',
            $this->get('oro_grid.datagrid.request_params')
                 ->get(RequestParameters::ADDITIONAL_PARAMETERS)
        )) {
            $origin = $user->getImapConfiguration();
            if ($origin) {
                $this->get('oro_imap.email_synchronizer')
                     ->syncOrigins(array($origin->getId()));
            }
        }

        $output = array(
            'entity' => $user,
        );

        if ($editRoute) {
            $output = array_merge($output, array('editRoute' => $editRoute));
        }

        return $output;
    }

    /**
     * @return BusinessUnitManager
     */
    protected function getBusinessUnitManager()
    {
        return $this->get('oro_organization.business_unit_manager');
    }
}

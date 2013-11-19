<?php

namespace Oro\Bundle\UserBundle\Controller;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\PersistentCollection;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\Acl;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserApi;
use Oro\Bundle\UserBundle\Autocomplete\UserSearchHandler;

use Oro\Bundle\OrganizationBundle\Entity\Manager\BusinessUnitManager;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\EntityMetadata;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

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
        $user = $this->get('oro_user.manager')->createUser();

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
        $output = array(
            'entity'   => $user,
            'dynamic'  => $this->getDynamicFields($user)
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

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * TODO: will be refactored via twig extension
     */
    protected function getDynamicFields(User $entity)
    {
        /** @var \Oro\Bundle\EntityConfigBundle\Config\ConfigManager $configManager */
        $configManager  = $this->get('oro_entity_config.config_manager');
        $extendProvider = $this->get('oro_entity_config.provider.extend');
        $entityProvider = $this->get('oro_entity_config.provider.entity');
        $viewProvider   = $this->get('oro_entity_config.provider.view');

        $fields = $extendProvider->filter(
            function (ConfigInterface $config) use ($viewProvider, $extendProvider) {
                $extendConfig = $extendProvider->getConfigById($config->getId());

                return
                    $config->is('owner', ExtendManager::OWNER_CUSTOM)
                    && !$config->is('state', ExtendManager::STATE_NEW)
                    && !$config->is('is_deleted')
                    && $viewProvider->getConfigById($config->getId())->is('is_displayable')
                    && !(
                        in_array($extendConfig->getId()->getFieldType(), array('oneToMany', 'manyToOne', 'manyToMany'))
                        && $extendProvider->getConfig($extendConfig->get('target_entity'))->is('is_deleted', true)
                    );
            },
            get_class($entity)
        );

        $dynamicRow = array();

        foreach ($fields as $field) {
            $fieldName = $field->getId()->getFieldName();
            $value = $entity->{'get' . ucfirst(Inflector::camelize($fieldName))}();

            /** Prepare DateTime field type */
            if ($value instanceof \DateTime) {
                $configFormat = $this->get('oro_config.global')->get('oro_locale.date_format') ? : 'Y-m-d';
                $value        = $value->format($configFormat);
            }

            /** Prepare Relation field type */
            if ($value instanceof PersistentCollection) {
                $collection     = $value;
                $extendConfig   = $extendProvider->getConfigById($field->getId());
                $titleFieldName = $extendConfig->get('target_title');

                /** generate link for related entities collection */
                $route       = false;
                $routeParams = false;

                if (class_exists($extendConfig->get('target_entity'))) {
                    /** @var EntityMetadata $metadata */
                    $metadata = $configManager->getEntityMetadata($extendConfig->get('target_entity'));
                    if ($metadata && $metadata->routeView) {
                        $route       = $metadata->routeView;
                        $routeParams = array(
                            'id' => null
                        );
                    }

                    $relationExtendConfig = $extendProvider->getConfig($extendConfig->get('target_entity'));
                    if ($relationExtendConfig->is('owner', ExtendManager::OWNER_CUSTOM)) {
                        $route       = 'oro_entity_view';
                        $routeParams = array(
                            'entity_id' => str_replace('\\', '_', $extendConfig->get('target_entity')),
                            'id'        => null
                        );
                    }
                }

                $value = array(
                    'route'        => $route,
                    'route_params' => $routeParams,
                    'values'       => array()
                );

                foreach ($collection as $item) {
                    $routeParams['id'] = $item->getId();

                    $title = [];
                    foreach ($titleFieldName as $fieldName) {
                        $title[] = $item->{Inflector::camelize('get_' . $fieldName)}();
                    }

                    $value['values'][] = array(
                        'id'    => $item->getId(),
                        'link'  => $route ? $this->generateUrl($route, $routeParams) : false,
                        'title' => implode(' ', $title)
                    );
                }
            }

            $fieldName = $field->getId()->getFieldName();
            $dynamicRow[$entityProvider->getConfigById($field->getId())->get('label') ? : $fieldName]
                       = $value;
        }

        return $dynamicRow;
    }
}

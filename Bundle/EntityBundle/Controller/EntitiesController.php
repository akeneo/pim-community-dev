<?php

namespace Oro\Bundle\EntityBundle\Controller;

use Doctrine\Common\Inflector\Inflector;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use FOS\Rest\Util\Codes;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;

use Oro\Bundle\EntityBundle\ORM\OroEntityManager;
use Oro\Bundle\EntityBundle\Datagrid\CustomEntityDatagrid;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

use Oro\Bundle\SecurityBundle\SecurityFacade;

/**
 * Entities controller.
 * @Route("/entity")
 * todo: Discuss ACL permissions for controller
 */
class EntitiesController extends Controller
{
    /**
     * Grid of Custom/Extend entity.
     * @Route(
     *      "/{id}",
     *      name="oro_entity_index",
     *      defaults={"id"=0}
     * )
     * @Template()
     */
    public function indexAction($id)
    {
        $extendEntityName = str_replace('_', '\\', $id);
        $this->checkAccess('VIEW', $extendEntityName);

        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->get('oro_entity_config.provider.entity');

        if (!$entityConfigProvider->hasConfig($extendEntityName)) {
            throw $this->createNotFoundException();
        }

        $entityConfig = $entityConfigProvider->getConfig($extendEntityName);

        /** @var  CustomEntityDatagrid $datagrid */
        $datagridManager = $this->get('oro_entity.custom_datagrid.manager');

        $datagridManager->setCustomEntityClass($extendEntityName);
        $datagridManager->setEntityName($extendEntityName);
        $datagridManager->getRouteGenerator()->setRouteParameters(array('id' => $id));

        $view = $datagridManager->getDatagrid()->createView();

        return 'json' == $this->getRequest()->getRequestFormat()
            ? $this->get('oro_grid.renderer')->renderResultsJsonResponse($view)
            : $this->render(
                'OroEntityBundle:Entities:index.html.twig',
                array(
                    'datagrid'     => $view,
                    'entity_id'    => $id,
                    'entity_class' => $extendEntityName,
                    'label'        => $entityConfig->get('label')
                )
            );
    }

    /**
     * @Route(
     *      "/info/{id}/{className}/{fieldName}",
     *      name="oro_entity_info",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0, "className"="", "fieldName"=""}
     * )
     * @Template
     */
    public function infoAction($id, $className, $fieldName)
    {
        $className = str_replace('_', '\\', $className);
        $this->checkAccess('VIEW', $className);

        $extendProvider = $this->get('oro_entity_config.provider.extend');
        $entityProvider = $this->get('oro_entity_config.provider.entity');
        $viewProvider   = $this->get('oro_entity_config.provider.view');

        $fields = $extendProvider->filter(
            function (ConfigInterface $config) use ($viewProvider) {
                return
                    $config->is('owner', ExtendManager::OWNER_CUSTOM)
                    && !$config->is('state', ExtendManager::STATE_NEW)
                    && !$config->is('is_deleted')
                    && $viewProvider->getConfigById($config->getId())->is('is_displayable');
            },
            $className
        );

        $dynamicRow = array();
        foreach ($fields as $field) {
            //$label = $entityProvider->getConfigById($field->getId())->get('label') ? : $field->getId()->getFieldName();
            //$dynamicRow[$label] = $contact->{'get' . ucfirst(Inflector::camelize($field->getId()->getFieldName()))}();
        }

        /*return array(
            'dynamic' => $dynamicRow,
            'entity'  => $entity
        );*/
    }

    /**
     * Grid of Custom/Extend entity.
     * @Route(
     *      "/relation/{id}/{className}/{fieldName}",
     *      name="oro_entity_relation",
     *      defaults={"id"=0, "className"="", "fieldName"=""}
     * )
     * @Template()
     */
    public function relationAction($id, $className, $fieldName)
    {
        $extendEntityName = str_replace('_', '\\', $className);
        $this->checkAccess('VIEW', $extendEntityName);

        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->get('oro_entity_config.provider.entity');
        $extendConfigProvider = $this->get('oro_entity_config.provider.extend');

        if (!$entityConfigProvider->hasConfig($extendEntityName)) {
            throw $this->createNotFoundException();
        }

        $entityConfig = $entityConfigProvider->getConfig($extendEntityName);

        $fieldConfig = $extendConfigProvider->getConfig($extendEntityName, $fieldName);

        /** @var  CustomEntityDatagrid $datagrid */
        $datagridManager = $this->get('oro_entity.relation_datagrid.manager');
        $datagridManager->setCustomEntityClass($fieldConfig->get('target_entity'));
        $datagridManager->setRelationConfig($fieldConfig);
        $datagridManager->setRelation($this->getDoctrine()->getRepository($extendEntityName)->find($id));
        $datagridManager->setAdditionalParameters(
            array(
                'data_in' => explode(',', $this->getRequest()->get('added')),
                'data_not_in' => explode(',', $this->getRequest()->get('removed'))
            )
        );
        $datagridManager->setEntityName($fieldConfig->get('target_entity'));

        $view = $datagridManager->getDatagrid()->createView();




        return 'json' == $this->getRequest()->getRequestFormat()
            ? $this->get('oro_grid.renderer')->renderResultsJsonResponse($view)
            : array(
                'datagrid'        => $view,
                'entity_id'       => $className,
                'entity_class'    => $extendEntityName,
                'label'           => $entityConfig->get('label'),
                'entity_provider' => $entityConfigProvider,
                'relation'        => $fieldConfig
              );
    }


    /**
     * View custom entity instance.
     * @Route(
     *      "/view/{entity_id}/item/{id}",
     *      name="oro_entity_view",
     *      defaults={"entity_id"=0, "id"=0}
     * )
     * @Template()
     */
    public function viewAction($entity_id, $id)
    {
        $extendEntityName = str_replace('_', '\\', $entity_id);
        $this->checkAccess('VIEW', $extendEntityName);

        /** @var OroEntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $entityConfigProvider = $this->get('oro_entity_config.provider.entity');
        $extendConfigProvider = $this->get('oro_entity_config.provider.extend');
        $viewConfigProvider   = $this->get('oro_entity_config.provider.view');

        $extendEntityRepository = $em->getRepository($extendEntityName);

        $record = $extendEntityRepository->find($id);

        $fields = $viewConfigProvider->filter(
            function (ConfigInterface $config) use ($extendConfigProvider) {
                $extendConfig = $extendConfigProvider->getConfigById($config->getId());
                return $config->is('is_displayable') && $extendConfig->is('is_deleted', false);
            },
            $extendEntityName
        );

        $result = array();
        foreach ($fields as $field) {
            $value = $record->{'get' . Inflector::classify($field->getId()->getFieldName())}();
            if ($value instanceof \DateTime) {
                $configFormat = $this->get('oro_config.global')->get('oro_locale.date_format') ? : 'Y-m-d';
                $value        = $value->format($configFormat);
            }

            $fieldConfig = $entityConfigProvider->getConfigById($field->getId());

            $result[$fieldConfig->get('label') ? : $field->getId()->getFieldName()] = $value;
        }

        return array(
            'parent'        => $entity_id,
            'entity'        => $record,
            'entity_fields' => $result,
            'id'            => $id,
            'entity_config' => $entityConfigProvider->getConfig($extendEntityName),
            'entity_class'  => $extendEntityName,
        );
    }

    /**
     * Update custom entity instance.
     * @Route(
     *      "/update/{entity_id}/item/{id}",
     *      name="oro_entity_update",
     *      defaults={"entity_id"=0, "id"=0}
     * )
     * @Template()
     */
    public function updateAction(Request $request, $entity_id, $id)
    {
        $extendEntityName = str_replace('_', '\\', $entity_id);
        $this->checkAccess(!$id ? 'CREATE' : 'EDIT', $extendEntityName);

        /** @var OroEntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->get('oro_entity_config.provider.entity');
        $entityConfig         = $entityConfigProvider->getConfig($extendEntityName);

        $extendEntityRepository = $em->getRepository($extendEntityName);

        $record = !$id ? new $extendEntityName : $extendEntityRepository->find($id);

        $form = $this->createForm(
            'custom_entity_type',
            $record,
            array(
                'class_name'   => $extendEntityName,
                'block_config' => array(),
            )
        );

        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {

                $em->persist($record);
                $em->flush();

                $id = $record->getId();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('oro.entity.controller.message.saved')
                );

                return $this->get('oro_ui.router')->actionRedirect(
                    array(
                        'route'      => 'oro_entity_update',
                        'parameters' => array(
                            'entity_id' => $entity_id,
                            'id'        => $id
                        ),
                    ),
                    array(
                        'route'      => 'oro_entity_view',
                        'parameters' => array(
                            'entity_id' => $entity_id,
                            'id'        => $id
                        )
                    )
                );
            }
        }

        return array(
            'entity'        => $record,
            'entity_id'     => $entity_id,
            'entity_config' => $entityConfig,
            'entity_class'  => $extendEntityName,
            'form'          => $form->createView(),
        );
    }

    /**
     * Delete custom entity instance.
     * @Route(
     *      "/delete/{entity_id}/item/{id}",
     *      name="oro_entity_delete",
     *      defaults={"entity_id"=0, "id"=0}
     * )
     */
    public function deleteAction($entity_id, $id)
    {
        $extendEntityName = str_replace('_', '\\', $entity_id);
        $this->checkAccess('DELETE', $extendEntityName);

        /** @var OroEntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $extendEntityRepository = $em->getRepository($extendEntityName);

        $record = $extendEntityRepository->find($id);
        if (!$record) {
            return new JsonResponse('', Codes::HTTP_FORBIDDEN);
        }

        $em->remove($record);
        $em->flush();

        return new JsonResponse('', Codes::HTTP_OK);
    }

    /**
     * Checks if an access to the given entity is granted or not
     *
     * @param string $permission
     * @param string $entityName
     * @return bool
     * @throws AccessDeniedException
     */
    private function checkAccess($permission, $entityName)
    {
        /** @var SecurityFacade $securityFacade */
        $securityFacade = $this->get('oro_security.security_facade');
        $isGranted      = $securityFacade->isGranted($permission, 'entity:' . $entityName);
        if (!$isGranted) {
            throw new AccessDeniedException('Access denied.');
        }
    }
}

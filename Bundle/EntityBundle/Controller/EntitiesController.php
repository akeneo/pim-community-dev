<?php

namespace Oro\Bundle\EntityBundle\Controller;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\PersistentCollection;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use FOS\Rest\Util\Codes;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\EntityBundle\ORM\OroEntityManager;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Entity\OptionSetRelation;
use Oro\Bundle\EntityConfigBundle\Entity\Repository\OptionSetRelationRepository;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityConfigBundle\Metadata\EntityMetadata;

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

        return [
            'entity_id'    => $id,
            'entity_class' => $extendEntityName,
            'label'        => $entityConfig->get('label')
        ];
    }

    /**
     * @Route(
     *      "/detailed/{id}/{className}/{fieldName}",
     *      name="oro_entity_detailed",
     *      defaults={"id"=0, "className"="", "fieldName"=""}
     * )
     * @Template
     *
     * @param integer $id        Related entity ID
     * @param string $className  Self ClassName
     * @param string $fieldName  Self FieldName (relation description)
     *
     * @return array
     */
    public function detailedAction($id, $className, $fieldName)
    {
        $className = str_replace('_', '\\', $className);
        $this->checkAccess('VIEW', $className);

        $entityProvider = $this->get('oro_entity_config.provider.entity');
        $extendProvider = $this->get('oro_entity_config.provider.extend');
        $relationConfig = $extendProvider->getConfig($className, $fieldName);

        $fields = $extendProvider->filter(
            function (ConfigInterface $config) use ($relationConfig) {
                return
                    !$config->is('state', ExtendManager::STATE_NEW)
                    && !$config->is('is_deleted')
                    && in_array($config->getId()->getFieldName(), $relationConfig->get('target_detailed'));
            },
            $relationConfig->get('target_entity')
        );

        $entity = $this->getDoctrine()->getRepository($relationConfig->get('target_entity'))->find($id);
        if ($entity->getId()) {
            $dynamicRow = array();
            foreach ($fields as $field) {
                $fieldName          = $field->getId()->getFieldName();
                $label              = $entityProvider->getConfigById($field->getId())->get('label') ? : $fieldName;
                $dynamicRow[$label] = $entity->{Inflector::camelize('get_' . $fieldName)}();
            }

            return array(
                'dynamic' => $dynamicRow,
                'entity'  => $entity
            );
        }
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
        $fieldConfig  = $extendConfigProvider->getConfig($extendEntityName, $fieldName);

        return [
            'entity_id'       => $className,
            'entity_class'    => $extendEntityName,
            'label'           => $entityConfig->get('label'),
            'entity_provider' => $entityConfigProvider,
            'extend_provider' => $extendConfigProvider,
            'relation'        => $fieldConfig
        ];
    }


    /**
     * View custom entity instance.
     * @Route(
     *      "/view/{entity_id}/item/{id}",
     *      name="oro_entity_view",
     *      defaults={"entity_id"=0, "id"=0}
     * )
     * @Template()
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * TODO: will be refactored via twig extension
     */
    public function viewAction($entity_id, $id)
    {
        $extendEntityName = str_replace('_', '\\', $entity_id);
        $this->checkAccess('VIEW', $extendEntityName);

        /** @var OroEntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var \Oro\Bundle\EntityConfigBundle\Config\ConfigManager $configManager */
        $configManager = $this->get('oro_entity_config.config_manager');

        $entityConfigProvider   = $this->get('oro_entity_config.provider.entity');
        $extendConfigProvider   = $this->get('oro_entity_config.provider.extend');
        $viewConfigProvider     = $this->get('oro_entity_config.provider.view');
        $extendEntityRepository = $em->getRepository($extendEntityName);
        $record                 = $extendEntityRepository->find($id);

        $fields = $viewConfigProvider->filter(
            function (ConfigInterface $config) use ($extendConfigProvider) {
                $extendConfig = $extendConfigProvider->getConfigById($config->getId());

                return
                    $config->is('is_displayable')
                    && $extendConfig->is('is_deleted', false)
                    && !(
                        in_array($extendConfig->getId()->getFieldType(), array('oneToMany', 'manyToOne', 'manyToMany'))
                        && $extendConfigProvider->getConfig($extendConfig->get('target_entity'))->is('is_deleted', true)
                    );
            },
            $extendEntityName
        );

        $result = [];
        foreach ($fields as $field) {
            $value = $record->{'get' . Inflector::classify($field->getId()->getFieldName())}();

            /** Prepare OptionSet field type */
            if ($field->getId()->getFieldType() == 'optionSet') {
                /** @var OptionSetRelationRepository */
                $osr = $em->getRepository(OptionSetRelation::ENTITY_NAME);

                $model = $extendConfigProvider->getConfigManager()->getConfigFieldModel(
                    $field->getId()->getClassName(),
                    $field->getId()->getFieldName()
                );

                $value = $osr->findByFieldId($model->getId(), $id);
                array_walk(
                    $value,
                    function (&$item) {
                        $item = ['title' => $item->getOption()->getLabel()];
                    }
                );

                $value['values'] = $value;
            }

            /** Prepare DateTime field type */
            if ($value instanceof \DateTime) {
                $dateFormat = 'Y-m-d';
                if ($this->get('oro_config.global')->get('oro_locale.date_format')) {
                    $dateFormat = $this->get('oro_config.global')->get('oro_locale.date_format');
                }
                $configFormat = $dateFormat;
                $value        = $value->format($configFormat);
            }

            /** Prepare Relation field type */
            if ($value instanceof PersistentCollection) {
                $collection     = $value;
                $extendConfig   = $extendConfigProvider->getConfigById($field->getId());
                $titleFieldName = $extendConfig->get('target_title');
                $targetEntity   = $extendConfig->get('target_entity');

                /** generate link for related entities collection */
                $route = $routeParams = false;
                if (class_exists($extendConfig->get('target_entity'))) {
                    /** @var EntityMetadata $metadata */
                    $metadata = $configManager->getEntityMetadata($targetEntity);
                    if ($metadata && $metadata->routeView) {
                        $route       = $metadata->routeView;
                        $routeParams = ['id' => null];
                    }

                    $relationExtendConfig = $extendConfigProvider->getConfig($targetEntity);
                    if ($relationExtendConfig->is('owner', ExtendManager::OWNER_CUSTOM)) {
                        $route       = 'oro_entity_view';
                        $routeParams = ['entity_id' => str_replace('\\', '_', $targetEntity), 'id' => null];
                    }
                }

                $value = array(
                    'route'        => $route,
                    'route_params' => $routeParams,
                    'values'       => []
                );

                foreach ($collection as $item) {
                    $title = [];
                    foreach ($titleFieldName as $fieldName) {
                        $title[] = $item->{Inflector::camelize('get_' . $fieldName)}();
                    }

                    $routeParams['id'] = $item->getId();
                    $value['values'][] = [
                        'id'    => $item->getId(),
                        'link'  => $route ? $this->generateUrl($route, $routeParams) : false,
                        'title' => implode(' ', $title)
                    ];
                }
            }

            $fieldConfig = $entityConfigProvider->getConfigById($field->getId());
            $label       = $field->getId()->getFieldName();
            if ($fieldConfig->get('label')) {
                $label = $fieldConfig->get('label');
            }
            $result[$label] = $value;
        }

        return [
            'parent'        => $entity_id,
            'entity'        => $record,
            'entity_fields' => $result,
            'id'            => $id,
            'entity_config' => $entityConfigProvider->getConfig($extendEntityName),
            'entity_class'  => $extendEntityName,
        ];
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
                'block_config' => array(
                    'general' => array(
                        'title'    => 'General',
                        'priority' => -1
                    )
                ),
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
                    [
                        'route'      => 'oro_entity_update',
                        'parameters' => [
                            'entity_id' => $entity_id,
                            'id'        => $id
                        ],
                    ],
                    [
                        'route'      => 'oro_entity_view',
                        'parameters' => [
                            'entity_id' => $entity_id,
                            'id'        => $id
                        ]
                    ]
                );
            }
        }

        return [
            'entity'        => $record,
            'entity_id'     => $entity_id,
            'entity_config' => $entityConfig,
            'entity_class'  => $extendEntityName,
            'form'          => $form->createView(),
        ];
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

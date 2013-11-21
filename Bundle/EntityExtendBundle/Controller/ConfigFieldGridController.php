<?php

namespace Oro\Bundle\EntityExtendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use FOS\Rest\Util\Codes;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;

use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * Class ConfigGridController
 *
 * @package Oro\Bundle\EntityExtendBundle\Controller
 * @Route("/entity/extend/field")
 * TODO: Discuss ACL impl., currently acl is disabled
 * @AclAncestor("oro_entityconfig_manage")
 */
class ConfigFieldGridController extends Controller
{
    const SESSION_ID_FIELD_TYPE = '_extendbundle_create_entity_%s_field_type';
    const SESSION_ID_FIELD_NAME = '_extendbundle_create_entity_%s_field_name';

    /**
     * @Route("/create/{id}", name="oro_entityextend_field_create", requirements={"id"="\d+"}, defaults={"id"=0})
     * Acl(
     *      id="oro_entityextend_field_create",
     *      label="Create custom field",
     *      type="action",
     *      group_name=""
     * )
     *
     * @Template
     */
    public function createAction(EntityConfigModel $entity)
    {
        /** @var ExtendManager $extendManager */
        $extendManager = $this->get('oro_entity_extend.extend.extend_manager');

        if (!$extendManager->isExtend($entity->getClassName())) {
            $this->get('session')->getFlashBag()->add('error', $entity->getClassName() . 'isn\'t extend');

            return $this->redirect(
                $this->generateUrl(
                    'oro_entityconfig_fields',
                    array(
                        'id' => $entity->getId()
                    )
                )
            );
        }

        $newFieldModel = new FieldConfigModel();
        $newFieldModel->setEntity($entity);

        $form = $this->createForm(
            'oro_entity_extend_field_type',
            $newFieldModel,
            array('class_name' => $entity->getClassName())
        );
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {
                $request->getSession()->set(
                    sprintf(self::SESSION_ID_FIELD_NAME, $entity->getId()),
                    $newFieldModel->getFieldName()
                );
                $request->getSession()->set(
                    sprintf(self::SESSION_ID_FIELD_TYPE, $entity->getId()),
                    $newFieldModel->getType()
                );

                return $this->redirect(
                    $this->generateUrl(
                        'oro_entityextend_field_update',
                        array(
                            'id' => $entity->getId()
                        )
                    )
                );

            }
        }

        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->get('oro_entity_config.provider.entity');

        return array(
            'form'          => $form->createView(),
            'entity_id'     => $entity->getId(),
            'entity_config' => $entityConfigProvider->getConfig($entity->getClassName()),
        );
    }

    /**
     * @Route("/update/{id}", name="oro_entityextend_field_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * Acl(
     *      id="oro_entityextend_field_update",
     *      label="Update custom field",
     *      type="action",
     *      group_name=""
     * )
     */
    public function updateAction(EntityConfigModel $entity)
    {
        $request   = $this->getRequest();
        $fieldName = $request->getSession()->get(sprintf(self::SESSION_ID_FIELD_NAME, $entity->getId()));
        $fieldType = $request->getSession()->get(sprintf(self::SESSION_ID_FIELD_TYPE, $entity->getId()));
        if (!$fieldName || !$fieldType) {
            return $this->redirect($this->generateUrl('oro_entityextend_field_create', ['id' => $entity->getId()]));
        }

        /** @var ConfigManager $configManager */
        $configManager      = $this->get('oro_entity_config.config_manager');
        $extendProvider     = $configManager->getProvider('extend');
        $extendEntityConfig = $extendProvider->getConfig($entity->getClassName());

        $relationValues  = [];
        $relationOptions = explode('||', $fieldType);
        $relationName    = $relationOptions[0];

        if (isset($relationOptions[1])) {
            $fieldName = $relationOptions[1];
        }

        $relationOptions = explode('|', $relationOptions[0]);

        /**
         * fieldType example: relation|manyToOne|testentity5_relation_7
         * check if fieldType has 3rd option [fieldName]
         */
        if (count($relationOptions) == 4) {
            $fieldType = ExtendHelper::getReversRelationType($relationOptions[0]);

            $relations = $extendEntityConfig->get('relation');
            if (isset($relations[$relationName])) {
                $relationValues['target_entity'] = $relations[$relationName]['target_entity'];
                $relationValues['relation_key']  = $relationName;
            }
        }

        $newFieldModel = $configManager->createConfigFieldModel($entity->getClassName(), $fieldName, $fieldType);

        $extendFieldConfig = $extendProvider->getConfig($entity->getClassName(), $fieldName);
        $extendFieldConfig->set('owner', ExtendManager::OWNER_CUSTOM);
        $extendFieldConfig->set('state', ExtendManager::STATE_NEW);
        $extendFieldConfig->set('extend', true);

        foreach ($relationValues as $key => $value) {
            $extendFieldConfig->set($key, $value);
        }

        $form = $this->createForm('oro_entity_config_type', null, ['config_model' => $newFieldModel]);

        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {
                //persist data inside the form
                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('oro.entity_extend.controller.config_field.message.saved')
                );

                if ($extendEntityConfig->get('state') != ExtendManager::STATE_NEW) {
                    $extendEntityConfig->set('state', ExtendManager::STATE_UPDATED);
                }

                $extendEntityConfig->set('upgradeable', true);

                $configManager->persist($extendEntityConfig);
                $configManager->flush();

                return $this->get('oro_ui.router')->actionRedirect(
                    ['route' => 'oro_entityconfig_field_update', 'parameters' => ['id' => $newFieldModel->getId()]],
                    ['route' => 'oro_entityconfig_view', 'parameters' => ['id' => $entity->getId()]]
                );
            }
        }

        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->get('oro_entity_config.provider.entity');

        $entityConfig = $entityConfigProvider->getConfig($entity->getClassName());
        $fieldConfig  = $entityConfigProvider->getConfig($entity->getClassName(), $newFieldModel->getFieldName());

        return $this->render(
            'OroEntityConfigBundle:Config:fieldUpdate.html.twig',
            [
                'entity_config' => $entityConfig,
                'field_config'  => $fieldConfig,
                'field'         => $newFieldModel,
                'form'          => $form->createView(),
                'formAction'    => $this->generateUrl('oro_entityextend_field_update', array('id' => $entity->getId())),
                'require_js'    => $configManager->getProvider('extend')->getPropertyConfig()->getRequireJsModules()
            ]
        );
    }

    /**
     * @Route(
     *      "/remove/{id}",
     *      name="oro_entityextend_field_remove",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * Acl(
     *      id="oro_entityextend_field_remove",
     *      label="Remove custom field",
     *      type="action",
     *      group_name=""
     * )
     */
    public function removeAction(FieldConfigModel $field)
    {
        if (!$field) {
            throw $this->createNotFoundException('Unable to find FieldConfigModel entity.');
        }

        $className = $field->getEntity()->getClassName();

        /** @var ExtendManager $extendManager */
        $extendManager = $this->get('oro_entity_extend.extend.extend_manager');
        /** @var ConfigManager $configManager */
        $configManager = $this->get('oro_entity_config.config_manager');

        $fieldConfig = $extendManager->getConfigProvider()->getConfig($className, $field->getFieldName());

        if (!$fieldConfig->is('owner', ExtendManager::OWNER_CUSTOM)) {
            return new Response('', Codes::HTTP_FORBIDDEN);
        }

        $fieldConfig->set('state', ExtendManager::STATE_DELETED);

        $fields = $extendManager->getConfigProvider()->filter(
            function (ConfigInterface $config) {
                return in_array(
                    $config->get('state'),
                    array(ExtendManager::STATE_ACTIVE, ExtendManager::STATE_UPDATED)
                );
            },
            $className
        );

        if (!count($fields)) {
            $entityConfig = $extendManager->getConfigProvider()->getConfig($className);

            $entityConfig->set('upgradeable', false);
            $configManager->persist($entityConfig);
        }

        $configManager->persist($fieldConfig);
        $configManager->flush();

        return new JsonResponse(array('message' => 'Item was removed.', 'successful' => true), Codes::HTTP_OK);
    }

    /**
     * @Route(
     *      "/unremove/{id}",
     *      name="oro_entityextend_field_unremove",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * Acl(
     *      id="oro_entityextend_field_unremove",
     *      label="UnRemove custom field",
     *      type="action",
     *      group_name=""
     * )
     */
    public function unremoveAction(FieldConfigModel $field)
    {
        if (!$field) {
            throw $this->createNotFoundException('Unable to find FieldConfigModel entity.');
        }

        $className = $field->getEntity()->getClassName();

        /** @var ExtendManager $extendManager */
        $extendManager = $this->get('oro_entity_extend.extend.extend_manager');
        /** @var ConfigManager $configManager */
        $configManager = $this->get('oro_entity_config.config_manager');

        $fieldConfig = $extendManager->getConfigProvider()->getConfig($className, $field->getFieldName());

        if (!$fieldConfig->is('owner', ExtendManager::OWNER_CUSTOM)) {
            return new Response('', Codes::HTTP_FORBIDDEN);
        }

        $fieldConfig->set('state', ExtendManager::STATE_UPDATED);

        $configManager->persist($fieldConfig);

        $entityConfig = $extendManager->getConfigProvider()->getConfig($className);
        $entityConfig->set('upgradeable', true);

        $configManager->persist($entityConfig);

        $configManager->flush();

        return new JsonResponse(array('message' => 'Item was restored', 'successful' => true), Codes::HTTP_OK);
    }
}

<?php

namespace Oro\Bundle\EntityExtendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use FOS\Rest\Util\Codes;

use Oro\Bundle\UserBundle\Annotation\Acl;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;

use Oro\Bundle\EntityExtendBundle\Form\Type\FieldType;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

/**
 * Class ConfigGridController
 * @package Oro\Bundle\EntityExtendBundle\Controller
 * @Route("/entityextend/field")
 * @Acl(
 *      id="oro_entityextend",
 *      name="Entity extend manipulation",
 *      description="Entity extend manipulation"
 * )
 */
class ConfigFieldGridController extends Controller
{

    const SESSION_ID_FIELD_TYPE = '_extendbundle_create_entity_%s_field_type';
    const SESSION_ID_FIELD_NAME = '_extendbundle_create_entity_%s_field_name';

    /**
     * @Route("/create/{id}", name="oro_entityextend_field_create", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Acl(
     *      id="oro_entityextend_field_create",
     *      name="Create custom field",
     *      description="Update entity create custom field",
     *      parent="oro_entityextend"
     * )
     * @Template
     */
    public function createAction(EntityConfigModel $entity)
    {
        /** @var ExtendManager $extendManager */
        $extendManager = $this->get('oro_entity_extend.extend.extend_manager');

        if (!$extendManager->isExtend($entity->getClassName())) {
            $this->get('session')->getFlashBag()->add('error', $entity->getClassName() . 'isn\'t extend');

            return $this->redirect($this->generateUrl('oro_entityconfig_fields',
                array(
                    'id' => $entity->getId()
                )
            ));
        }

        $newFieldModel = new FieldConfigModel();
        $newFieldModel->setEntity($entity);

        $form    = $this->createForm(new FieldType(), $newFieldModel);
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

                return $this->redirect($this->generateUrl('oro_entityextend_field_update',
                    array(
                        'id' => $entity->getId()
                    )
                ));

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
     * @Acl(
     *      id="oro_entityextend_field_update",
     *      name="Update custom field",
     *      description="Update entity update custom field",
     *      parent="oro_entityextend"
     * )
     */
    public function updateAction(EntityConfigModel $entity)
    {
        $request = $this->getRequest();

        $fieldName = $request->getSession()->get(sprintf(self::SESSION_ID_FIELD_NAME, $entity->getId()));
        $fieldType = $request->getSession()->get(sprintf(self::SESSION_ID_FIELD_TYPE, $entity->getId()));

        if (!$fieldName || !$fieldType) {
            return $this->redirect($this->generateUrl('oro_entityextend_field_create',
                array(
                    'id' => $entity->getId()
                )
            ));
        }

        /** @var ConfigManager $configManager */
        $configManager = $this->get('oro_entity_config.config_manager');
        $newFieldModel = $configManager->createConfigFieldModel($entity->getClassName(), $fieldName, $fieldType);

        $extendFieldConfig = $configManager->getProvider('extend')->getConfig($entity->getClassName(), $fieldName);
        $extendFieldConfig->set('owner', ExtendManager::OWNER_CUSTOM);
        $extendFieldConfig->set('is_extend', true);

        $form = $this->createForm('oro_entity_config_type', null, array(
            'config_model' => $newFieldModel,
        ));

        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {
                //persist data inside the form
                $this->get('session')->getFlashBag()->add('success', 'ConfigField successfully saved');

                return $this->get('oro_ui.router')->actionRedirect(
                    array(
                        'route'      => 'oro_entityconfig_field_update',
                        'parameters' => array('id' => $newFieldModel->getId()),
                    ),
                    array(
                        'route'      => 'oro_entityconfig_view',
                        'parameters' => array('id' => $entity->getId())
                    )
                );
            }
        }

        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->get('oro_entity_config.provider.entity');
        $entityConfig         = $entityConfigProvider->getConfig($entity->getClassName());
        $fieldConfig          = $entityConfigProvider->getConfig(
            $entity->getClassName(),
            $newFieldModel->getFieldName()
        );

        return $this->render(
            'OroEntityConfigBundle:Config:fieldUpdate.html.twig',
            array(
                'entity_config' => $entityConfig,
                'field_config'  => $fieldConfig,
                'field'         => $newFieldModel,
                'form'          => $form->createView(),
                'formAction'    => $this->generateUrl('oro_entityextend_field_update', array('id' => $entity->getId()))
            ));
    }

    /**
     * @Route(
     *      "/remove/{id}",
     *      name="oro_entityextend_field_remove",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * @Acl(
     *      id="oro_entityextend_field_remove",
     *      name="Remove custom field",
     *      description="Update entity remove custom field",
     *      parent="oro_entityextend"
     * )
     */
    public function removeAction(FieldConfigModel $field)
    {
        if (!$field) {
            throw $this->createNotFoundException('Unable to find ConfigField entity.');
        }

        /** @var ExtendManager $extendManager */
        $extendManager = $this->get('oro_entity_extend.extend.extend_manager');

        $fieldConfig = $extendManager->getConfigProvider()->getConfig(
            $field->getEntity()->getClassName(),
            $field->getFieldName()
        );

        if (!$fieldConfig->is('is_extend')) {
            return new Response('', Codes::HTTP_FORBIDDEN);
        }

        $this->getDoctrine()->getManager()->remove($field);
        $this->getDoctrine()->getManager()->flush($field);

        $extendManager->getConfigProvider()->clearCache(
            $field->getEntity()->getClassName(),
            $field->getFieldName()
        );

        return new Response('', Codes::HTTP_NO_CONTENT);
    }
}

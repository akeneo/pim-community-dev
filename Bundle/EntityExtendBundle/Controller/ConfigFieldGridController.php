<?php

namespace Oro\Bundle\EntityExtendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use FOS\Rest\Util\Codes;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityExtendBundle\Form\Type\FieldType;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\EntityConfigBundle\ConfigManager;
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

        $request = $this->getRequest();
        $form    = $this->createForm(new FieldType());

        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {
                $data = $form->getData();

                if ($entity->getField($data['code'])) {
                    $form->get('code')->addError(new FormError(sprintf(
                        "Field '%s' already exist in entity '%s', ", $data['code'], $entity->getClassName()
                    )));
                } else {
                    $extendManager->getConfigFactory()->createFieldConfig($entity->getClassName(), $data);

                    /** @var ConfigManager $configManager */
                    $configManager = $this->get('oro_entity_config.config_manager');
                    $configManager->clearCache($entity->getClassName());

                    $this->get('session')->getFlashBag()->add('success', sprintf(
                        'field "%s" has been added to entity "%', $data['code'], $entity->getClassName()
                    ));

                    return $this->redirect($this->generateUrl('oro_entityconfig_field_update',
                        array(
                            'id' => $entity->getField($data['code'])->getId()
                        )
                    ));
                }
            }
        }

        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->get('oro_entity.config.entity_config_provider');

        return array(
            'form'      => $form->createView(),
            'entity_id' => $entity->getId(),
            'entity_config' => $entityConfigProvider->getConfig($entity->getClassName()),
        );
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

        $fieldConfig = $extendManager->getConfigProvider()
            ->getFieldConfig($field->getEntity()->getClassName(), $field->getFieldName());
        if (!$fieldConfig->is('is_extend')) {
            return new Response('', Codes::HTTP_FORBIDDEN);
        }

        $this->getDoctrine()->getManager()->remove($field);
        $this->getDoctrine()->getManager()->flush($field);

        /** @var ConfigManager $configManager */
        $configManager = $this->get('oro_entity_config.config_manager');
        $configManager->clearCache($fieldConfig->getClassName());

        return new Response('', Codes::HTTP_NO_CONTENT);
    }
}

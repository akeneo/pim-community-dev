<?php


namespace Oro\Bundle\EntityExtendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\EntityExtendBundle\Exception\RuntimeException;
use Oro\Bundle\EntityExtendBundle\Form\Type\FieldType;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigField;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigEntity;
use Oro\Bundle\EntityConfigBundle\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

/**
 * Class ConfigGridController
 * @package Oro\Bundle\EntityExtendBundle\Controller
 * @Route("/entityextend/field")
 */
class ConfigFieldGridController extends Controller
{
    /**
     * @Route("/create/{id}", name="oro_entityextend_field_create", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     */
    public function createAction($id)
    {
        /** @var ConfigEntity $entity */
        $entity = $this->getDoctrine()->getRepository(ConfigEntity::ENTITY_NAME)->find($id);
        /** @var ExtendManager $extendManager */
        $extendManager = $this->get('oro_entity_extend.extend.extend_manager');

        if (!$extendManager->isExtend($entity->getClassName())) {
            $this->get('session')->getFlashBag()->add('error', $entity->getClassName() . 'isn\'t extend');

            return $this->redirect($this->generateUrl('oro_entityconfig_fields',
                array(
                    'id' => $id
                )
            ));
        }

        $request = $this->getRequest();
        $form    = $this->createForm(new FieldType());

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $data = $form->getData();

                if ($entity->getField($data['code'])) {
                    $form->get('code')->addError(new FormError(sprintf(
                        "Field '%s' already exist in entity '%s', ", $data['code'], $entity->getClassName()
                    )));
                } else {
                    $extendManager->getConfigFactory()->createFieldConfig($entity->getClassName(), $data);

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

        return array(
            'form'      => $form->createView(),
            'entity_id' => $id
        );
    }

    /**
     * @Route("/remove/{id}", name="oro_entityextend_field_remove", requirements={"id"="\d+"}, defaults={"id"=0})
     */
    public function removeAction(ConfigField $field)
    {
        /** @var ExtendManager $extendManager */
        $extendManager = $this->get('oro_entity_extend.extend.extend_manager');

        if (!$extendManager->isExtend($field->getEntity()->getClassName())) {
            throw new RuntimeException('Cannot delete not extend field');
        }

        $this->getDoctrine()->getManager()->remove($field);
        $this->getDoctrine()->getManager()->flush($field);

        /** @var ConfigManager $configManager */
        $configManager = $this->get('oro_entity_config.config_manager');
        $configManager->clearCache();

        return new RedirectResponse($this->getRequest()->headers->get('referer'));
    }
}

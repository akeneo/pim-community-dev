<?php

namespace Oro\Bundle\EntityExtendBundle\Controller;

use Oro\Bundle\EntityConfigBundle\Config\FieldConfig;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigEntity;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityExtendBundle\Form\Type\UniqueKeysType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class ConfigGridController
 * @package Oro\Bundle\EntityExtendBundle\Controller
 * @Route("/entityextend/entity")
 */
class ConfigEntityGridController extends Controller
{
    /**
     * @Route("/unique-key/{id}", name="oro_entityextend_entity_unique_key", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     */
    public function uniqueAction(ConfigEntity $entity)
    {
        /** @var ConfigProvider $configProvider */
        $configProvider = $this->get('oro_entity_extend.config.extend_config_provider');
        $entityConfig   = $configProvider->getConfig($entity->getClassName());

        $data = unserialize($entityConfig->get('unique_key'));

        $request = $this->getRequest();
        $form    = $this->createForm(new UniqueKeysType($entityConfig->getFields(function (FieldConfig $fieldConfig) {
            return $fieldConfig->getType() != 'ref-many';
        })), $data);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $entityConfig->set('unique_key', serialize($form->getData()));
                $configProvider->persist($entityConfig);
                $configProvider->flush();

                return $this->redirect($this->generateUrl('oro_entityconfig_index'));
            }
        }

        return array(
            'form'      => $form->createView(),
            'entity_id' => $entity->getId()
        );
    }
}

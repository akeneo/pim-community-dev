<?php

namespace Oro\Bundle\EntityExtendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Oro\Bundle\EntityExtendBundle\Form\Type\EntityType;

use Oro\Bundle\EntityConfigBundle\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;


use Oro\Bundle\EntityExtendBundle\Form\Type\UniqueKeyCollectionType;

/**
 * Class ConfigGridController
 * @package Oro\Bundle\EntityExtendBundle\Controller
 * @Route("/entityextend/entity")
 * @Acl(
 *      id="oro_entityextend",
 *      name="Entity extend manipulation",
 *      description="Entity extend manipulation"
 * )
 */
class ConfigEntityGridController extends Controller
{
    const SESSION_ID_CREATE_ENTITY = '_extendbundle_create_entity';

    /**
     * @Route(
     *      "/unique-key/{id}",
     *      name="oro_entityextend_entity_unique_key",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * @Acl(
     *      id="oro_entityextend_entity_unique_key",
     *      name="Unique keys",
     *      description="Update entity unique keys",
     *      parent="oro_entityextend"
     * )
     * @Template
     */
    public function uniqueAction(EntityConfigModel $entity)
    {
        /** @var ConfigProvider $configProvider */
        $configProvider = $this->get('oro_entity_config.provider.extend');
        $entityConfig   = $configProvider->getConfig($entity->getClassName());
        $fieldConfigIds = $configProvider->getConfigIds($entity->getClassName());

        $data = $entityConfig->has('unique_key') ? $entityConfig->get('unique_key') : array();

        $request = $this->getRequest();

        $form = $this->createForm(
            new UniqueKeyCollectionType(
                array_filter($fieldConfigIds,
                    function (FieldConfigId $fieldConfigId) {
                        return $fieldConfigId->getFieldType() != 'ref-many';
                    }
                )
            ),
            $data
        );

        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {
                $data = $form->getData();

                $error = false;
                $names = array();
                foreach ($data['keys'] as $key) {
                    if (in_array($key['name'], $names)) {
                        $error = true;
                        $this->get('session')->getFlashBag()->add(
                            'error',
                            sprintf('Name for key should be unique, key "%s" is not unique.', $key['name'])
                        );
                        break;
                    }

                    if (empty($key['name'])) {
                        $error = true;
                        $this->get('session')->getFlashBag()->add(
                            'error',
                            'Name of key can\'t be empty.'
                        );
                        break;
                    }

                    $names[] = $key['name'];
                }

                if (!$error) {
                    $entityConfig->set('unique_key', $data);
                    $configProvider->persist($entityConfig);
                    $configProvider->flush();

                    return $this->redirect(
                        $this->generateUrl('oro_entityconfig_view', array('id' => $entity->getId()))
                    );
                }
            }
        }

        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->get('oro_entity_config.provider.entity');

        return array(
            'form'          => $form->createView(),
            'entity_id'     => $entity->getId(),
            'entity_config' => $entityConfigProvider->getConfig($entity->getClassName())
        );
    }

    /**
     * @Route("/create", name="oro_entityextend_entity_create")
     * @Acl(
     *      id="oro_entityextend_entity_create",
     *      name="Create custom entity",
     *      description="Create custom entity",
     *      parent="oro_entityextend"
     * )
     * @Template
     */
    public function createAction()
    {
        $entityModel = new EntityConfigModel;
        $form        = $this->createForm(new EntityType(), $entityModel);
        $request     = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {
                $request->getSession()->set(self::SESSION_ID_CREATE_ENTITY, $entityModel->getClassName());

                return $this->redirect($this->generateUrl('oro_entityextend_entity_update'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/update", name="oro_entityextend_entity_update")
     * @Acl(
     *      id="oro_entityextend_entity_update",
     *      name="Update entity",
     *      description="Update configurable entity",
     *      parent="oro_entityextend"
     * )
     * @Template()
     */
    public function updateAction()
    {
        $request = $this->getRequest();

        $className = $request->getSession()->get(self::SESSION_ID_CREATE_ENTITY);

        if (!$className ) {
            return $this->redirect($this->generateUrl('oro_entityextend_entity_create'));
        }

        /** @var ConfigManager $configManager */
        $configManager = $this->get('oro_entity_config.config_manager');
        $newEntityModel = $configManager->createConfigEntityModel($className, true);
        $extendConfig = $configManager->getProvider('extend')->getConfig($className);
        $extendConfig->set('owner', ExtendManager::OWNER_CUSTOM);
        $extendConfig->set('is_extend', true);
        $form = $this->createForm('oro_entity_config_type', null, array(
            'config_model' => $newEntityModel,
        ));

        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {
                $newEntityModel = $configManager->createConfigFieldModel($className, 'id', 'integer');
                $extendFieldConfig = $configManager->getProvider('extend')->getConfig($className, 'id');
                $extendFieldConfig->set('owner', ExtendManager::OWNER_CUSTOM);
                $extendFieldConfig->set('is_extend', true);
                $configManager->persist($extendFieldConfig);

                $configManager->flush();

                //persist data inside the form
                $this->get('session')->getFlashBag()->add('success', 'ConfigEntity successfully saved');

                return $this->get('oro_ui.router')->actionRedirect(
                    array(
                        'route'      => 'oro_entityconfig_update',
                        'parameters' => array('id' => $newEntityModel->getId()),
                    ),
                    array(
                        'route' => 'oro_entityconfig_index'
                    )
                );
            }
        }

        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->get('oro_entity.config.entity_config_provider');

        return array(
            'entity'        => $newEntityModel,
            'entity_config' => $entityConfigProvider->getConfig($className),
            'form'          => $form->createView(),
        );
    }
}

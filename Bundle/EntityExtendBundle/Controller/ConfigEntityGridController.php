<?php

namespace Oro\Bundle\EntityExtendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use FOS\Rest\Util\Codes;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Oro\Bundle\EntityExtendBundle\Form\Type\EntityType;
use Oro\Bundle\EntityExtendBundle\Form\Type\UniqueKeyCollectionType;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * Class ConfigGridController
 * @package Oro\Bundle\EntityExtendBundle\Controller
 * @Route("/entity/extend/entity")
 * TODO: Discuss ACL impl., currently acl is disabled
 * @AclAncestor("oro_entityconfig_manage")
 */
class ConfigEntityGridController extends Controller
{
    /**
     * @Route(
     *      "/unique-key/{id}",
     *      name="oro_entityextend_entity_unique_key",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * Acl(
     *      id="oro_entityextend_entity_unique_key",
     *      label="Unique entity unique keys",
     *      type="action",
     *      group_name=""
     * )
     * @Template
     */
    public function uniqueAction(EntityConfigModel $entity)
    {
        /** @var ConfigProvider $configProvider */
        $configProvider = $this->get('oro_entity_config.provider.extend');
        $entityConfig   = $configProvider->getConfig($entity->getClassName());
        $fieldConfigIds = $configProvider->getIds($entity->getClassName());

        $data = $entityConfig->has('unique_key') ? $entityConfig->get('unique_key') : array();

        $request = $this->getRequest();

        $form = $this->createForm(
            new UniqueKeyCollectionType(
                array_filter(
                    $fieldConfigIds,
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
                        $this->get('session')->getFlashBag()->add('error', 'Name of key can\'t be empty.');

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
     * Acl(
     *      id="oro_entityextend_entity_create",
     *      label="Create custom entity",
     *      type="action",
     *      group_name=""
     * )
     * @Template
     */
    public function createAction()
    {
        $request = $this->getRequest();

        /** @var ConfigManager $configManager */
        $configManager = $this->get('oro_entity_config.config_manager');

        $className = '';
        if ($request->getMethod() == 'POST') {
            $className = 'Extend\\Entity\\' . $request->request->get(
                'oro_entity_config_type[model][className]',
                null,
                true
            );
        }

        $entityModel  = $configManager->createConfigEntityModel($className);
        $extendConfig = $configManager->getProvider('extend')->getConfig($className);
        $extendConfig->set('owner', ExtendManager::OWNER_CUSTOM);
        $extendConfig->set('state', ExtendManager::STATE_NEW);
        $extendConfig->set('upgradeable', false);
        $extendConfig->set('is_extend', true);

        $configManager->persist($extendConfig);

        $form = $this->createForm(
            'oro_entity_config_type',
            null,
            array(
                'config_model' => $entityModel,
            )
        );

        $cloneEntityModel = clone $entityModel;
        $cloneEntityModel->setClassName('');
        $form->add(
            'model',
            new EntityType,
            array(
                'data' => $cloneEntityModel,
            )
        );

        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {
                //persist data inside the form
                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('oro.entity_extend.controller.config_entity.message.saved')
                );

                return $this->get('oro_ui.router')->actionRedirect(
                    array(
                        'route'      => 'oro_entityconfig_update',
                        'parameters' => array('id' => $entityModel->getId()),
                    ),
                    array(
                        'route'      => 'oro_entityconfig_view',
                        'parameters' => array('id' => $entityModel->getId()),
                    )
                );
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route(
     *      "/remove/{id}",
     *      name="oro_entityextend_entity_remove",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * Acl(
     *      id="oro_entityextend_entity_remove",
     *      label="Remove custom entity",
     *      type="action",
     *      group_name=""
     * )
     */
    public function removeAction(EntityConfigModel $entity)
    {
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find EntityConfigModel entity.');
        }

        /** @var ExtendManager $extendManager */
        $extendManager = $this->get('oro_entity_extend.extend.extend_manager');
        /** @var ConfigManager $configManager */
        $configManager = $this->get('oro_entity_config.config_manager');

        $entityConfig = $extendManager->getConfigProvider()->getConfig($entity->getClassName());

        if ($entityConfig->get('owner') == ExtendManager::OWNER_SYSTEM) {
            return new Response('', Codes::HTTP_FORBIDDEN);
        }

        $entityConfig->set('state', ExtendManager::STATE_DELETED);

        $configManager->persist($entityConfig);
        $configManager->flush();

        return new JsonResponse(array('message' => 'Item was removed', 'successful' => true), Codes::HTTP_OK);
    }

    /**
     * @Route(
     *      "/unremove/{id}",
     *      name="oro_entityextend_entity_unremove",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * Acl(
     *      id="oro_entityextend_entity_unremove",
     *      label="Unremove custom entity",
     *      type="action",
     *      group_name=""
     * )
     */
    public function unremoveAction(EntityConfigModel $entity)
    {
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find EntityConfigModel entity.');
        }

        /** @var ExtendManager $extendManager */
        $extendManager = $this->get('oro_entity_extend.extend.extend_manager');
        /** @var ConfigManager $configManager */
        $configManager = $this->get('oro_entity_config.config_manager');

        $entityConfig = $extendManager->getConfigProvider()->getConfig($entity->getClassName());

        if ($entityConfig->get('owner') == ExtendManager::OWNER_SYSTEM) {
            return new Response('', Codes::HTTP_FORBIDDEN);
        }

        $entityConfig->set('state', ExtendManager::STATE_UPDATED);

        $configManager->persist($entityConfig);
        $configManager->flush();

        return new JsonResponse(array('message' => 'Item was restored', 'successful' => true), Codes::HTTP_OK);
    }
}

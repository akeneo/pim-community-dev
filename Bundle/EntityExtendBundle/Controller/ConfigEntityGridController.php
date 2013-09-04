<?php

namespace Oro\Bundle\EntityExtendBundle\Controller;

use FOS\Rest\Util\Codes;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigIdInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityExtendBundle\Form\Type\EntityType;
use Oro\Bundle\EntityExtendBundle\Form\Type\UniqueKeyCollectionType;

/**
 * Class ConfigGridController
 * @package Oro\Bundle\EntityExtendBundle\Controller
 * @Route("/entity/extend/entity")
 * @Acl(
 *      id="oro_entityextend",
 *      name="Entity extend manipulation",
 *      description="Entity extend manipulation"
 * )
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
        $fieldConfigIds = $configProvider->getIds($entity->getClassName());

        $data = $entityConfig->has('unique_key') ? $entityConfig->get('unique_key') : array();

        $request = $this->getRequest();

        $form = $this->createForm(
            new UniqueKeyCollectionType(
                array_filter(
                    $fieldConfigIds,
                    function (FieldConfigIdInterface $fieldConfigId) {
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
        $request = $this->getRequest();

        /** @var ConfigManager $configManager */
        $configManager = $this->get('oro_entity_config.config_manager');

        /** @var ExtendManager $extendManager */
        $extendManager = $this->get('oro_entity_extend.extend.extend_manager');

        $className = '';
        if ($request->getMethod() == 'POST') {
            $className = $request->request->get('oro_entity_config_type[model][className]', null, true);
        }

        $entityModel  = $configManager->createConfigEntityModel($className);
        $extendConfig = $configManager->getProvider('extend')->getConfig($className);
        $extendConfig->set('owner', ExtendManager::OWNER_CUSTOM);
        $extendConfig->set('state', ExtendManager::STATE_NEW);
        $extendConfig->set('is_extend', true);

        $extendClass = $extendManager->getClassGenerator()->generateExtendClassName($className);
        $proxyClass  = $extendManager->getClassGenerator()->generateProxyClassName($className);

        $extendConfig->set('extend_class', $extendClass);
        $extendConfig->set('proxy_class', $proxyClass);

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
                $this->get('session')->getFlashBag()->add('success', 'ConfigEntity successfully saved');

                return $this->get('oro_ui.router')->actionRedirect(
                    array(
                        'route'      => 'oro_entityconfig_update',
                        'parameters' => array('id' => $entityModel->getId()),
                    ),
                    array(
                        'route' => 'oro_entityconfig_index'
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
     * @Acl(
     *      id="oro_entityextend_entity_remove",
     *      name="Remove custom entity",
     *      description="Remove custom entity",
     *      parent="oro_entityextend"
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
     * @Acl(
     *      id="oro_entityextend_entity_unremove",
     *      name="Unremove custom entity",
     *      description="Unremove custom entity",
     *      parent="oro_entityextend"
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

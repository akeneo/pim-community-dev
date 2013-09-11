<?php

namespace Oro\Bundle\EntityExtendBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Process\Process;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;

use Oro\Bundle\EntityExtendBundle\Tools\Schema;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

/**
 * EntityExtendBundle controller.
 * @Route("/entity/extend")
 */
class ApplyController extends Controller
{
    /**
     * @Route(
     *      "/apply/{id}",
     *      name="oro_entityextend_apply",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * @Acl(
     *      id="oro_entityextend_apply",
     *      name="Validate entityconfig changes",
     *      type="action",
     *      group=""
     * )
     * @Template()
     */
    public function applyAction($id)
    {
        /** @var EntityConfigModel $entity */
        $entity = $this->getDoctrine()->getRepository(EntityConfigModel::ENTITY_NAME)->find($id);

        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->get('oro_entity_config.provider.entity');

        /** @var ConfigProvider $extendConfigProvider */
        $extendConfigProvider = $this->get('oro_entity_config.provider.extend');
        $extendConfig         = $extendConfigProvider->getConfig($entity->getClassName());
        $extendFieldConfigs   = $extendConfigProvider->getConfigs($entity->getClassName());

        /** @var Schema $schemaTools */
        $schemaTools = $this->get('oro_entity_extend.tools.schema');

        /**
         * do Validations
         */
        $validation = array();

        /** @var Config $fieldConfig */
        foreach ($extendFieldConfigs as $fieldConfig) {
            $isSystem = $fieldConfig->get('owner') == 'System' ? true : false;
            if ($isSystem) {
                continue;
            }

            if (in_array($fieldConfig->get('state'), array('New', 'Requires update', 'To be deleted'))) {
                if ($fieldConfig->get('state') == 'New') {
                    $isValid = true;
                } else {
                    $isValid = $schemaTools->checkFieldCanDelete($fieldConfig->getId());
                }

                if ($isValid) {
                    $validation['success'][] = sprintf(
                        "Field '%s(%s)' is valid. State -> %s",
                        $fieldConfig->getId()->getFieldName(),
                        $fieldConfig->get('owner'),
                        $fieldConfig->get('state')
                    );
                } else {
                    $validation['error'][] = sprintf(
                        "Warning. Field '%s(%s)' has data.",
                        $fieldConfig->getId()->getFieldName(),
                        $fieldConfig->get('owner')
                    );
                }
            }
        }

        $entityConfig = $entityConfigProvider->getConfig($entity->getClassName());

        return array(
            'validations'   => $validation,
            'entity'        => $entity,
            'entity_config' => $entityConfig,
            'entity_extend' => $extendConfig,
        );
    }

    /**
     * @Route(
     *      "/update/{id}",
     *      name="oro_entityextend_update",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * @Acl(
     *      id="oro_entityextend_update",
     *      name="Apply entityconfig changes",
     *      type="action",
     *      group=""
     * )
     * @Template()
     */
    public function updateAction($id)
    {
        /** @var EntityConfigModel $entity */
        $entity = $this->getDoctrine()->getRepository(EntityConfigModel::ENTITY_NAME)->find($id);
        $env    = $this->get('kernel')->getEnvironment();

        $commands = array(
            'backup'       => new Process(
                'php ../app/console oro:entity-extend:backup ' . str_replace(
                    '\\',
                    '\\\\',
                    $entity->getClassName()
                ) . ' --env ' . $env
            ),
            'update'       => new Process('php ../app/console oro:entity-extend:update' . ' --env ' . $env),
            'cacheClear'   => new Process('php ../app/console cache:clear --no-warmup' . ' --env ' . $env),
            'schemaUpdate' => new Process('php ../app/console doctrine:schema:update --force' . ' --env ' . $env),
            'searchIndex'  => new Process('php ../app/console oro:search:create-index --env ' . $env),
            'cacheWarmup'  => new Process('php ../app/console cache:warmup' . ' --env ' . $env),
        );

        foreach ($commands as $command) {
            /** @var $command Process */
            $command->run();

            while ($command->isRunning()) {
                /** wait for previous process */
            }
        }

        /** @var ConfigProvider $extendConfigProvider */
        $extendConfigProvider = $this->get('oro_entity_config.provider.extend');
        $extendConfig         = $extendConfigProvider->getConfig($entity->getClassName());
        $extendFieldConfigs   = $extendConfigProvider->getConfigs($entity->getClassName());
        $entityState          = $extendConfig->get('state');

        foreach ($extendFieldConfigs as $fieldConfig) {
            if ($fieldConfig->get('owner') != ExtendManager::OWNER_SYSTEM
                && $fieldConfig->get('state') != ExtendManager::STATE_DELETED
            ) {
                $fieldConfig->set('state', ExtendManager::STATE_ACTIVE);
            }

            if ($fieldConfig->get('state') == ExtendManager::STATE_DELETED) {
                $fieldConfig->set('is_deleted', true);
            }

            $extendConfigProvider->persist($fieldConfig);
        }

        $extendConfigProvider->flush();

        $extendConfig->set('state', $entityState);
        if ($extendConfig->get('state') == ExtendManager::STATE_DELETED) {
            $extendConfig->set('is_deleted', true);
        } else {
            $extendConfig->set('state', ExtendManager::STATE_ACTIVE);
        }

        $extendConfigProvider->persist($extendConfig);
        $extendConfigProvider->flush();

        return $this->redirect($this->generateUrl('oro_entityconfig_index'));
    }
}

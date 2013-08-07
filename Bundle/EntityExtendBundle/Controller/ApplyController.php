<?php

namespace Oro\Bundle\EntityExtendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Process\Process;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\EntityExtendBundle\Tools\Schema;

/**
 * EntityExtendBundle controller.
 * @Route("/oro_entityextend")
 * @Acl(
 *      id="oro_entityextend",
 *      name="Entity extend manipulation",
 *      description="Entity extend manipulation"
 * )
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
     *      name="Validate changes",
     *      description="Validate entityconfig changes",
     *      parent="oro_entityextend"
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
        $extendFieldConfigIds = $extendConfigProvider->getFieldConfigIds($entity->getClassName());

        /** @var Schema $schemaTools */
        $schemaTools = $this->get('oro_entity_extend.tools.schema');

        /**
         * do Validations
         */
        $validation = array();

        foreach ($extendFieldConfigIds as $fieldConfigId) {
            /** @var  $fieldConfig */
            $fieldConfig = $extendConfigProvider->getConfig($fieldConfigId);
            //$isSystem = $schemaTools->checkFieldIsSystem($field);
            $isSystem = $fieldConfig->get('owner') == 'System' ? true : false;
            if ($isSystem) {
                continue;
            }

            if (in_array($fieldConfig->get('state'), array('New', 'Updated', 'To be deleted'))) {
                if ($fieldConfig->get('state') == 'New') {
                    $isValid = true;
                } else {
                    $isValid = $schemaTools->checkFieldCanDelete($fieldConfigId);
                }

                if ($isValid) {
                    $validation['success'][] = sprintf(
                        "Field '%s(%s)' is valid. State -> %s",
                        $fieldConfigId->getFieldName(),
                        $fieldConfig->get('owner'),
                        $fieldConfig->get('state')
                    );
                } else {
                    $validation['error'][] = sprintf(
                        "Warning. Field '%s(%s)' has data.",
                        $fieldConfigId->getFieldName(),
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
     *      name="Apply changes",
     *      description="Apply entityconfig changes",
     *      parent="oro_entityextend"
     * )
     * @Template()
     */
    public function updateAction($id)
    {
        /** @var EntityConfigModel $entity */
        $entity = $this->getDoctrine()->getRepository(EntityConfigModel::ENTITY_NAME)->find($id);
        $env    = $this->get('kernel')->getEnvironment();

        $commands = array(
            'backup'       => new Process('php ../app/console oro:entity-extend:backup ' . str_replace('\\', '\\\\', $entity->getClassName()) . ' --env ' . $env),
            'generator'    => new Process('php ../app/console oro:entity-extend:generate' . ' --env ' . $env),
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
        $extendFieldConfigIds = $extendConfigProvider->getFieldConfigIds($entity->getClassName());

        $extendConfig->set('state', 'Active');
        $extendConfigProvider->persist($extendConfig);

        foreach ($extendFieldConfigIds as $fieldId) {
            $fieldConfig = $extendConfigProvider->getConfigById($fieldId);
            if ($fieldConfig->get('owner') != 'System') {
                $fieldConfig->set('state', 'Active');
                $extendConfigProvider->persist($fieldConfig);
            }
        }

        $extendConfigProvider->flush();

        return $this->redirect($this->generateUrl('oro_entityconfig_view', array('id' => $id)));
    }
}

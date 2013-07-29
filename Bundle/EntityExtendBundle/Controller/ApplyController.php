<?php

namespace Oro\Bundle\EntityExtendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityConfigBundle\Entity\ConfigEntity;

use Oro\Bundle\EntityExtendBundle\Tools\Schema;
use Symfony\Component\Process\Process;

/**
 * EntityExtendBundle controller.
 * @Route("/oro_entityextend")
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
     * @ Acl(
     *      id="oro_entityextend_apply",
     *      name="Apply changes",
     *      description="Apply entityconfig changes",
     *      parent="oro_entityextend"
     * )
     * @Template()
     */
    public function applyAction($id)
    {
        /** @var ConfigEntity $entity */
        $entity  = $this->getDoctrine()->getRepository(ConfigEntity::ENTITY_NAME)->find($id);

        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->get('oro_entity.config.entity_config_provider');

        /** @var ConfigProvider $extendConfigProvider */
        $extendConfigProvider = $this->get('oro_entity_extend.config.extend_config_provider');
        $extendConfig = $extendConfigProvider->getConfig($entity->getClassName());

        /** @var Schema $schemaTools */
        $schemaTools = $this->get('oro_entity_extend.tools.schema');

        /**
         * do Validations
         */
        $validation = array();

        $fields = $extendConfig->getFields();
        foreach ($fields as $code => $field) {

            //$isSystem = $schemaTools->checkFieldIsSystem($field);
            $isSystem = $field->get('owner') == 'System' ? true : false;
            if ($isSystem) {
                continue;
            }

            if (in_array($field->get('state'), array('New', 'Updated', 'To be deleted'))) {
                if ($field->get('state') == 'New') {
                    $isValid = true;
                } else {
                    $isValid = $schemaTools->checkFieldCanDelete($field);
                }

                if ($isValid) {
                    $validation['success'][] = sprintf(
                        "Field '%s(%s)' is valid. State -> %s",
                        $code,
                        $field->get('owner'),
                        $field->get('state')
                    );
                } else {
                    $validation['error'][] = sprintf(
                        "Warning. Field '%s(%s)' has data.",
                        $code,
                        $field->get('owner')
                    );
                }
            }
        }

        return array(
            'validations'   => $validation,
            'entity'        => $entity,
            'entity_config' => $entityConfigProvider->getConfig($entity->getClassName()),
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
     * @ Acl(
     *      id="oro_entityextend_update",
     *      name="Apply changes",
     *      description="Apply entityconfig changes",
     *      parent="oro_entityextend"
     * )
     * @Template()
     */
    public function updateAction($id)
    {
        /** @var ConfigEntity $entity */
        $entity  = $this->getDoctrine()->getRepository(ConfigEntity::ENTITY_NAME)->find($id);
        $env = $this->get('kernel')->getEnvironment();

        $commands = array(
            'backup'       => new Process('../app/console oro:entity-extend:backup '. str_replace('\\', '\\\\', $entity->getClassName()). ' --env='.$env),
            'generator'    => new Process('../app/console oro:entity-extend:generate'. ' --env='.$env),
            'cacheClear'   => new Process('../app/console cache:clear --no-warmup'. ' --env='.$env),
            'schemaUpdate' => new Process('../app/console doctrine:schema:update --force'. ' --env='.$env),
            'cacheWarmup'  => new Process('../app/console cache:warmup'. ' --env='.$env),
        );

        foreach ($commands as $command) {
            /** @var $command Process */
            $command->run();

            while ($command->isRunning()) {
                /** wait for previous process */
            }
        }

        /** @var ConfigProvider $extendConfigProvider */
        $extendConfigProvider = $this->get('oro_entity_extend.config.extend_config_provider');
        $extendConfig = $extendConfigProvider->getConfig($entity->getClassName());

        $extendConfig->set('state', 'Active');

        foreach ($extendConfig->getFields() as $field) {
            if ($field->get('owner') != 'System') {
                $field->set('state', 'Active');
            }
        }

        $extendConfigProvider->persist($extendConfig);
        $extendConfigProvider->flush();

        return $this->redirect($this->generateUrl('oro_entityconfig_view', array('id' => $id)));
    }
}

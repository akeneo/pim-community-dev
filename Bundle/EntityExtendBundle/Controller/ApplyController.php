<?php

namespace Oro\Bundle\EntityExtendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityConfigBundle\Entity\ConfigEntity;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigField;

use Oro\Bundle\EntityExtendBundle\Tools\Schema;

/**
 * EntityExtendBundle controller.
 * @Route("/oro_entityextend")
 */
class ApplyController extends Controller
{
    /**
     * View Apply
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

        /** @var Schema $schemaTools */
        $schemaTools = $this->get('oro_entity_extend.tools.schema');

        /** @var ConfigProvider $extendConfigProvider */
        $extendConfigProvider = $this->get('oro_entity_extend.config.extend_config_provider');

        $extendConfig = $extendConfigProvider->getConfig($entity->getClassName());

        return array(
            'entity'        => $entity,
            'entity_config' => $entityConfigProvider->getConfig($entity->getClassName()),
            'entity_extend' => $extendConfig,
        );
    }
}

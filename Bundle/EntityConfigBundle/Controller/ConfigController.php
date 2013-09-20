<?php

namespace Oro\Bundle\EntityConfigBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\EntityConfigBundle\Metadata\EntityMetadata;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\GridBundle\Datagrid\Datagrid;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityConfigBundle\Datagrid\EntityFieldsDatagridManager;
use Oro\Bundle\EntityConfigBundle\Datagrid\ConfigDatagridManager;

use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;

/**
 * EntityConfig controller.
 * @Route("/entity/config")
 * @Acl(
 *      id="oro_entityconfig",
 *      name="Entity config manipulation",
 *      description="Entity config manipulation"
 * )
 */
class ConfigController extends Controller
{
    /**
     * Lists all Flexible entities.
     * @Route("/", name="oro_entityconfig_index")
     * @Acl(
     *      id="oro_entityconfig_index",
     *      name="View entities",
     *      description="View configurable entities",
     *      parent="oro_entityconfig"
     * )
     * @Template()
     */
    public function indexAction(Request $request)
    {
        /** @var  ConfigDatagridManager $datagrid */
        $datagridManager = $this->get('oro_entity_config.datagrid.manager');
        $datagrid        = $datagridManager->getDatagrid();
        $view            = 'json' == $request->getRequestFormat()
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'OroEntityConfigBundle:Config:index.html.twig';

        return $this->render(
            $view,
            array(
                'buttonConfig' => $datagridManager->getLayoutActions(),
                'datagrid'     => $datagrid->createView()
            )
        );
    }

    /**
     * @Route("/update/{id}", name="oro_entityconfig_update")
     * @Acl(
     *      id="oro_entityconfig_update",
     *      name="Update entity",
     *      description="Update configurable entity",
     *      parent="oro_entityconfig"
     * )
     * @Template()
     */
    public function updateAction($id)
    {
        $entity  = $this->getDoctrine()->getRepository(EntityConfigModel::ENTITY_NAME)->find($id);
        $request = $this->getRequest();

        $form = $this->createForm(
            'oro_entity_config_type',
            null,
            array(
                'config_model' => $entity,
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
                        'parameters' => array('id' => $id),
                    ),
                    array(
                        'route' => 'oro_entityconfig_view',
                        'parameters' => array('id' => $id)
                    )
                );
            }
        }

        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->get('oro_entity_config.provider.entity');

        return array(
            'entity'        => $entity,
            'entity_config' => $entityConfigProvider->getConfig($entity->getClassName()),
            'form'          => $form->createView(),
        );
    }

    /**
     * View Entity
     * @Route("/view/{id}", name="oro_entityconfig_view")
     * @Acl(
     *      id="oro_entityconfig_view",
     *      name="View entity",
     *      description="View configurable entity",
     *      parent="oro_entityconfig"
     * )
     * @Template()
     */
    public function viewAction(EntityConfigModel $entity)
    {
        /** @var  EntityFieldsDatagridManager $datagridManager */
        $datagridManager = $this->get('oro_entity_config.entityfieldsdatagrid.manager');
        $datagridManager->setEntityId($entity->getId());
        $datagridManager->getRouteGenerator()->setRouteParameters(
            array(
                'id' => $entity->getId()
            )
        );

        $datagrid = $datagridManager->getDatagrid();

        /**
         * define Entity module and name
         */
        $entityName = $moduleName = '';
        $className  = explode('\\', $entity->getClassName());
        if (count($className) > 1) {
            foreach ($className as $i => $name) {
                if (count($className) - 1 == $i) {
                    $entityName = $name;
                } elseif (!in_array($name, array('Bundle', 'Entity'))) {
                    $moduleName .= $name;
                }
            }
        } else {
            $entityName = $className[0];
            $moduleName = 'Custom';
        }

        /** @var \Oro\Bundle\EntityConfigBundle\Config\ConfigManager $configManager */
        $configManager = $this->get('oro_entity_config.config_manager');

        // generate link for Entity grid
        $link = '';
        /** @var EntityMetadata $metadata */
        if (class_exists($entity->getClassName())) {
            $metadata = $configManager->getEntityMetadata($entity->getClassName());

            if ($metadata && $metadata->routeName) {
                $link = $this->generateUrl($metadata->routeName);
            }
        }

        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->get('oro_entity_config.provider.entity');

        /** @var ConfigProvider $extendConfigProvider */
        $extendConfigProvider = $this->get('oro_entity_config.provider.extend');
        $extendConfig         = $extendConfigProvider->getConfig($entity->getClassName());

        /** @var ConfigProvider $ownershipConfigProvider */
        $ownershipConfigProvider = $this->get('oro_entity_config.provider.ownership');


        if (class_exists($entity->getClassName())) {
            /** @var QueryBuilder $qb */
            $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
            $qb->select('count(entity)');
            $qb->from($entity->getClassName(), 'entity');
            $entityCount = $qb->getQuery()->getSingleScalarResult();
        } else {
            $entityCount = 0;
        }

        return array(
            'entity'           => $entity,
            'entity_config'    => $entityConfigProvider->getConfig($entity->getClassName()),
            'entity_extend'    => $extendConfig,
            'entity_count'     => $entityCount,
            'entity_fields'    => $datagrid->createView(),
            'entity_ownership' => $ownershipConfigProvider->getConfig($entity->getClassName()),
            'unique_key'       => $extendConfig->get('unique_key'),
            'link'             => $link,
            'entity_name'      => $entityName,
            'module_name'      => $moduleName,
            'button_config'    => $datagridManager->getLayoutActions($entity),
            'require_js'       => $datagridManager->getRequireJsModules(),
        );
    }

    /**
     * Lists Entity fields
     * @Route("/fields/{id}", name="oro_entityconfig_fields", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template()
     */
    public function fieldsAction($id, Request $request)
    {
        $entity = $this->getDoctrine()->getRepository(EntityConfigModel::ENTITY_NAME)->find($id);

        /** @var  EntityFieldsDatagridManager $datagridManager */
        $datagridManager = $this->get('oro_entity_config.entityfieldsdatagrid.manager');
        $datagridManager->setEntityId($id);

        $datagrid = $datagridManager->getDatagrid();

        $datagridManager->getRouteGenerator()->setRouteParameters(
            array(
                'id' => $id
            )
        );

        $view = 'json' == $request->getRequestFormat()
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'OroEntityConfigBundle:Config:fields.html.twig';

        return $this->render(
            $view,
            array(
                'buttonConfig' => $datagridManager->getLayoutActions($entity),
                'datagrid'     => $datagrid->createView(),
                'entity_id'    => $id,
                'entity_name'  => $entity->getClassName(),
            )
        );
    }

    /**
     * @Route("/field/update/{id}", name="oro_entityconfig_field_update")
     * @Acl(
     *      id="oro_entityconfig_field_update",
     *      name="Update entity field",
     *      description="Update configurable entity field",
     *      parent="oro_entityconfig"
     * )
     * @Template()
     */
    public function fieldUpdateAction($id)
    {
        /** @var FieldConfigModel $field */
        $field   = $this->getDoctrine()->getRepository(FieldConfigModel::ENTITY_NAME)->find($id);
        $request = $this->getRequest();

        $form = $this->createForm(
            'oro_entity_config_type',
            null,
            array(
                'config_model' => $field,
            )
        );

        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {
                //persist data inside the form
                $this->get('session')->getFlashBag()->add('success', 'ConfigField successfully saved');

                return $this->get('oro_ui.router')->actionRedirect(
                    array(
                        'route'      => 'oro_entityconfig_field_update',
                        'parameters' => array('id' => $id),
                    ),
                    array(
                        'route'      => 'oro_entityconfig_view',
                        'parameters' => array('id' => $field->getEntity()->getId())
                    )
                );
            }
        }

        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->get('oro_entity_config.provider.entity');
        $entityConfig         = $entityConfigProvider->getConfig($field->getEntity()->getClassName());
        $fieldConfig          = $entityConfigProvider->getConfig(
            $field->getEntity()->getClassName(),
            $field->getFieldName()
        );

        return array(
            'entity_config' => $entityConfig,
            'field_config'  => $fieldConfig,
            'field'         => $field,
            'form'          => $form->createView(),
            'formAction'    => $this->generateUrl('oro_entityconfig_field_update', array('id' => $field->getId()))
        );
    }
}

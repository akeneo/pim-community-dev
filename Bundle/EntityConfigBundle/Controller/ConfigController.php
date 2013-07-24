<?php

namespace Oro\Bundle\EntityConfigBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\GridBundle\Datagrid\Datagrid;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityConfigBundle\Datagrid\EntityFieldsDatagridManager;
use Oro\Bundle\EntityConfigBundle\Datagrid\ConfigDatagridManager;

use Oro\Bundle\EntityConfigBundle\Entity\ConfigField;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigEntity;

/**
 * EntityConfig controller.
 * @Route("/oro_entityconfig")
 */
class ConfigController extends Controller
{
    /**
     * Lists all Flexible entities.
     * @Route("/", name="oro_entityconfig_index")
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
     * @Template()
     */
    public function updateAction($id)
    {
        $entity  = $this->getDoctrine()->getRepository(ConfigEntity::ENTITY_NAME)->find($id);
        $request = $this->getRequest();

        $form = $this->createForm(
            'oro_entity_config_config_entity_type',
            null,
            array(
                'class_name' => $entity->getClassName(),
                'entity_id'  => $entity->getId()
            )
        );

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                //persist data inside the form
                $this->get('session')->getFlashBag()->add('success', 'ConfigEntity successfully saved');

                return $this->get('oro_ui.router')->actionRedirect(
                    array(
                        'route' => 'oro_entityconfig_update',
                        'parameters' => array('id' => $id),
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
            'entity'        => $entity,
            'entity_config' => $entityConfigProvider->getConfig($entity->getClassName()),
            'form'          => $form->createView(),
        );
    }

    /**
     * View Entity
     * @Route("/view/{id}", name="oro_entityconfig_view")
     * @Template()
     */
    public function viewAction(ConfigEntity $entity)
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
        foreach ($className as $i => $name) {
            if (count($className) - 1 == $i) {
                $entityName = $name;
            } elseif (!in_array($name, array('Bundle', 'Entity'))) {
                $moduleName .= $name;
            }
        }

        /**
         * generate link for Entity grid
         */
        $link = $this->get('router')->match('/' . strtolower($entityName));
        if (is_array($link)) {
            $link = $this->generateUrl($link['_route']);
        }

        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->get('oro_entity.config.entity_config_provider');

        /** @var ConfigProvider $extendConfigProvider */
        $extendConfigProvider = $this->get('oro_entity_extend.config.extend_config_provider');
        $extendConfig = $extendConfigProvider->getConfig($entity->getClassName());

        return array(
            'entity'        => $entity,
            'entity_config' => $entityConfigProvider->getConfig($entity->getClassName()),
            'entity_extend' => $extendConfig,
            'entity_count'  => count($this->getDoctrine()->getRepository($entity->getClassName())->findAll()),
            'entity_fields' => $datagrid->createView(),

            'unique_key'    => $extendConfig->get('unique_key'),
            'link'          => $link,
            'entity_name'   => $entityName,
            'module_name'   => $moduleName,
            'button_config' => $datagridManager->getLayoutActions($entity),
        );
    }

    /**
     * Lists Entity fields
     * @Route("/fields/{id}", name="oro_entityconfig_fields", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template()
     */
    public function fieldsAction($id, Request $request)
    {
        $entity = $this->getDoctrine()->getRepository(ConfigEntity::ENTITY_NAME)->find($id);

        /** @var  FieldsDatagridManager $datagridManager */
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
     * @Template()
     */
    public function fieldUpdateAction($id)
    {
        $field = $this->getDoctrine()->getRepository(ConfigField::ENTITY_NAME)->find($id);

        $form  = $this->createForm(
            'oro_entity_config_config_field_type',
            null,
            array(
                'class_name' => $field->getEntity()->getClassName(),
                'field_name' => $field->getCode(),
                'field_type' => $field->getType(),
                'field_id'   => $field->getId(),
            )
        );
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                //persist data inside the form
                $this->get('session')->getFlashBag()->add('success', 'ConfigField successfully saved');

                return $this->get('oro_ui.router')->actionRedirect(
                    array(
                        'route' => 'oro_entityconfig_field_update',
                        'parameters' => array('id' => $id),
                    ),
                    array(
                        'route' => 'oro_entityconfig_view',
                        'parameters' => array('id' => $field->getEntity()->getId())
                    )
                );
            }
        }

        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->get('oro_entity.config.entity_config_provider');

        return array(
            'entity_config' => $entityConfigProvider->getConfig($field->getEntity()->getClassName()),
            'field_config'  => $entityConfigProvider->getFieldConfig($field->getEntity()->getClassName(), $field->getCode()),
            'field'         => $field,
            'form'          => $form->createView(),
        );
    }
}

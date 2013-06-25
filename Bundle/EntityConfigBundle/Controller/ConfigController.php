<?php

namespace Oro\Bundle\EntityConfigBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\GridBundle\Datagrid\Datagrid;
use Oro\Bundle\EntityConfigBundle\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Datagrid\ConfigDatagridManager;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigEntity;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigField;

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
        $datagrid = $this->get('oro_entity_config.datagrid.manager')->getDatagrid();
        $view     = 'json' == $request->getRequestFormat()
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'OroEntityConfigBundle:Config:index.html.twig';

        return $this->render(
            $view,
            array(
                //'buttons' =>
                'datagrid' => $datagrid->createView()
            )
        );
    }

    /**
     * Lists Entity fields
     * @Route("/fields/{id}", name="oro_entityconfig_fields", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template()
     */
    public function fieldsAction($id, Request $request)
    {
        /** @var  ConfigDatagridManager $datagridManager */
        $datagridManager = $this->get('oro_entity_config.fieldsdatagrid.manager');
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
                //'buttons' =>
                'datagrid' => $datagrid->createView()
            )
        );
    }

    /**
     * @Route("/view/{id}", name="oro_entityconfig_view")
     * @Template()
     */
    public function viewAction(ConfigEntity $entity)
    {
        return array(
            'entity' => $entity,
        );
    }

    /**
     * @Route("/fieldview/{id}", name="oro_entityconfig_fieldview")
     * @Template()
     */
    public function fieldviewAction(ConfigField $entity)
    {
        return array(
            'entity' => $entity,
        );
    }

    /**
     * @Route("/update/{id}", name="oro_entityconfig_update")
     * @Template()
     */
    public function updateAction(ConfigEntity $entity)
    {
        /** @var ConfigManager $configManager */
        $configManager = $this->get('oro_entity_config.config_manager');

        $formBuilder = $this->createFormBuilder();
        $data        = array();
        $formConfig  = array();
        foreach ($configManager->getProviders() as $provider) {
            $fields = array();
            foreach ($provider->getConfigContainer()->getEntityItems() as $code => $item) {
                if (isset($item['form']) && isset($item['form']['type']) && isset($item['form']['options'])) {
                    $formBuilder->add($code, $item['form']['type'], $item['form']['options']);
                    $config      = $provider->getConfig($entity->getClassName());
                    $data[$code] = $config->get($code);
                    $fields[]    = $code;
                }
            }
            if (count($fields)) {
                $formConfig[] = array(
                    'title'     => ucfirst($provider->getScope()),
                    'class' => '',
                    'subblocks' => array(
                        array(
                            'title'  => '',
                            'fields' => $fields,
                            'data'   => array(),
                        )
                    )
                );
            }
        }
        $formBuilder->setData($data);

        $form    = $formBuilder->getForm();
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                var_dump($form->getData());
            }
        }

        return array(
            'form' => $form->createView(),
            'formConfig' => $formConfig
        );
    }


    /**
     * @Route("/fieldupdate/{id}", name="oro_entityconfig_fieldupdate")
     * @Template()
     */
    public function fieldupdateAction(ConfigField $field)
    {
        /** @var ConfigManager $configManager */
        $configManager = $this->get('oro_entity_config.config_manager');

        $formBuilder = $this->createFormBuilder();
        $data        = array();
        $formConfig  = array();
        foreach ($configManager->getProviders() as $provider) {
            $fields = array();
            foreach ($provider->getConfigContainer()->getFieldItems() as $code => $item) {
                if (isset($item['form']) && isset($item['form']['type']) && isset($item['form']['options'])) {
                    $formBuilder->add($code, $item['form']['type'], $item['form']['options']);

                    $config      = $provider->getFieldConfig($field->getEntity()->getClassName(), $field->getCode());
                    $data[$code] = $config->get($code);
                    $fields[]    = $code;
                }
            }

            if (count($fields)) {
                $formConfig[] = array(
                    'title'     => ucfirst($provider->getScope()),
                    'class' => '',
                    'subblocks' => array(
                        array(
                            'title'  => '',
                            'fields' => $fields,
                            'data'   => array(),
                        )
                    )
                );
            }
        }

        $formBuilder->setData($data);
        $form    = $formBuilder->getForm();

        return array(
            'form' => $form->createView(),
            'formConfig' => $formConfig
        );
    }

    /**
     * Lists all Flexible entities.
     * @Route("/remove/{id}", name="oro_entityconfig_remove")
     * @Template()
     */
    public function removeAction($className)
    {
        var_dump($className);
        die;
    }
}

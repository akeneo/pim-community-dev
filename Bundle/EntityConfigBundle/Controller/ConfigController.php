<?php

namespace Oro\Bundle\EntityConfigBundle\Controller;

use Oro\Bundle\EntityConfigBundle\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Datagrid\ConfigDatagridManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\GridBundle\Datagrid\Datagrid;

use Oro\Bundle\EntityConfigBundle\Entity\ConfigEntity;

/**
 * User controller.
 *
 * @Route("/oro_entityconfig")
 */
class ConfigController extends Controller
{
    /**
     * Lists all Flexible entities.
     *
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
     *
     * @Route("/fields/{id}", name="oro_entityconfig_fields", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template()
     */
    public function fieldsAction($id, Request $request)
    {
        /** @var  ConfigDatagridManager $datagrid */
        $datagrid = $this->get('oro_entity_config.fieldsdatagrid.manager')->getDatagrid();
        $view     = 'json' == $request->getRequestFormat()
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
     * View Entity
     *
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
     * @Route("/update/{id}", name="oro_entityconfig_update")
     * @Template()
     */
    public function updateAction(ConfigEntity $entity)
    {
        /** @var ConfigManager $configManager */
        $configManager = $this->get('oro_entity_config.config_manager');

        $formBuilder = $this->createFormBuilder();
        $data        = array();
        foreach ($configManager->getProviders() as $provider) {
            foreach ($provider->getConfigContainer()->getEntityItems() as $code => $item) {
                if (isset($item['form']) && isset($item['form']['type']) && isset($item['form']['options'])) {
                    $formBuilder->add($code, $item['form']['type'], $item['form']['options']);
                    $config = $provider->getConfig($entity->getClassName());
                    $data[$code] = $config->get($code);
                }
            }
        }

        $formBuilder->setData($data);

        return array(
            'form' => $formBuilder->getForm()->createView()
        );
    }

    /**
     * Lists all Flexible entities.
     *
     * @Route("/update/{className}", name="oro_entityconfig_update")
     * @Template()
     */
    public function removeAction($className)
    {
        var_dump($className);
        die;
    }
}

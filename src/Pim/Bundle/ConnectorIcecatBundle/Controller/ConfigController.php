<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Controller;


use Pim\Bundle\ConnectorIcecatBundle\Form\Type\ConfigsType;
use Pim\Bundle\ConnectorIcecatBundle\Entity\Configs;
use Pim\Bundle\ConnectorIcecatBundle\Entity\ConfigManager;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Config Controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/config")
 */
class ConfigController extends Controller
{
    /**
     * Edit configuration of icecat connector
     *
     * @return Response
     *
     * @Route("/edit")
     * @Template()
     */
    public function editAction()
    {
        // get configuration values from database
        $configManager = $this->get('pim.connector_icecat.config_manager');
        $listConfigs = $configManager->getConfig();

        // Create a Configs entity
        $configs = new Configs($listConfigs);

        $form = $this->createForm(new ConfigsType(), $configs);

        return $this->render('PimConnectorIcecatBundle:Config:edit.html.twig', array(
                'form' => $form->createView(),
        ));
    }

    /**
     * Edits configuration
     *
     * @param Request $request
     *
     * @return Response
     *
     * @Method("POST")
     * @Route("/update")
     */
    public function updateAction(Request $request)
    {
        // get configuration values from database
        $manager = $this->get($this->getObjectManagerService());
        $configManager = $this->get('pim.connector_icecat.config_manager');
        $listConfigs = $configManager->getConfig();

        // Create a Configs entity
        $configs = new Configs($listConfigs);

        $form = $this->createForm(new ConfigsType(), $configs);
        $form->bind($request);

        if ($form->isValid()) {
            // persist data if valid
            try {
                foreach ($configs as $config) {
                    $manager->persists($config);
                }
                $manager->flush();
                $this->get('session')->setFlash('success', 'Config has been updated');

                return $this->redirect($this->generateUrl('pim_connectoricecat_config_edit'));
            } catch (\Exception $e) {
                $this->get('session')->setFlash('error', $e->getMessage());
            }
        }

        // render form with errors
        return $this->render(
            'PimConnectorIcecatBundle:Config:edit.html.twig', array('form' => $form->createView())
        );
    }

    /**
     * Get used object manager
     * @return string
     */
    protected function getObjectManagerService()
    {
        return 'doctrine.orm.entity_manager';
    }

    /**
     * @return \Pim\Bundle\ConnectorIcecatBundle\Model\ConfigManager
     */
    protected function getConfigManager()
    {
        return $this->get('pim.connector_icecat.config_manager');
    }
}

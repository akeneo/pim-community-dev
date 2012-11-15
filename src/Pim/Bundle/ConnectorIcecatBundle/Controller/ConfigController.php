<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Controller;

use Pim\Bundle\ConnectorIcecatBundle\Form\Type\ConfigsType;
use Pim\Bundle\ConnectorIcecatBundle\Entity\Configs;
use Pim\Bundle\ConnectorIcecatBundle\Entity\ConfigManager;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use APY\DataGridBundle\Grid\Source\Entity as GridEntity;

/**
 * @author    Romain Monceau @ Akeneo
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigController extends Controller
{
    /**
     * Edit configuration of icecat connector
     * @var Request $request
     * @Route("/config/edit")
     * @Template()
     */
    public function editAction(Request $request)
    {
        // get configuration values from database
        $em = $this->getDoctrine()->getEntityManager();
        $configManager = new ConfigManager($em);
        $listConfigs = $configManager->getConfig();
        
        // Create a Configs entity
        $configs = new Configs($listConfigs);
        
        $form = $this->createForm(new ConfigsType(), $configs);
        
        // validate form if method POST
        if ($request->isMethod('POST')) {
            $form->bind($request);
        
            if ($form->isValid()) {
                // if form is valid, save datas
                foreach ($configs as $config) {
                    $em->persists($config);
                }
                 
                $em->flush();

                $this->get('session')->setFlash('message', 'Insert with success');
            }
        }
        
        return $this->render('PimConnectorIcecatBundle:Config:edit.html.twig', array(
                'form' => $form->createView(),
        ));
    }
}
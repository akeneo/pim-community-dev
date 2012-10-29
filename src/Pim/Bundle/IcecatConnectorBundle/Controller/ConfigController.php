<?php
namespace Pim\Bundle\IcecatConnectorBundle\Controller;

use Pim\Bundle\IcecatConnectorBundle\Form\Type\ConfigsType;
use Pim\Bundle\IcecatConnectorBundle\Entity\Configs;
use Pim\Bundle\IcecatConnectorBundle\Entity\ConfigManager;

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
     * List Icecat configurations values in a grid
     * @Route("/config/list")
     * @Template()
     */
    public function listAction()
    {
        // creates simple grid based on entity (ORM)
        $source = new GridEntity('PimIcecatConnectorBundle:Config');
        $grid = $this->get('grid');
        $grid->setSource($source);
        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('PimIcecatConnectorBundle:Config:grid.html.twig');
    }
    
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
        
        return $this->render('PimIcecatConnectorBundle:Config:edit.html.twig', array(
                'form' => $form->createView(),
        ));
    }
}
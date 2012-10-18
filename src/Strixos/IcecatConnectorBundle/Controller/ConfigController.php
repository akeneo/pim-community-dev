<?php
namespace Strixos\IcecatConnectorBundle\Controller;

use Strixos\IcecatConnectorBundle\Form\Type\CollectionType;

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
        $source = new GridEntity('StrixosIcecatConnectorBundle:Config');
        $grid = $this->get('grid');
        $grid->setSource($source);
        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('StrixosIcecatConnectorBundle:Config:grid.html.twig');
    }
    
    /**
     * Edit configuration of icecat connector
     * @Route("/config/edit")
     * @Template()
     */
    public function editAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $configs = $em->getRepository('StrixosIcecatConnectorBundle:Config')->findAll();
        
        $form = $this->createForm(new CollectionType(), $configs);
        
        
        return $this->render('StrixosIcecatConnectorBundle:Config:edit.html.twig', array(
                'form' => $form->createView(),
        ));
    }
    
    /**
     * 
     * @var Request $request
     * @Route("/config/save")
     * @Template()
     */
    public function saveAction(Request $request)
    {
    	var_dump($_POST);
    	
    	
        // get current persisted config entities
        $em = $this->getDoctrine()->getEntityManager();
        $originalConfigs = $em->getRepository('StrixosIcecatConnectorBundle:Config')->findAll();
        
        
        $form = $this->createForm(new CollectionType(), $originalConfigs);
        
        if ($request->isMethod('POST')) {
        	$form->bindRequest($request);
        	
        	if ($form->isValid()) {
        		
        		// TODO : remove config rows
        		
        		// TODO : Add config rows
        		
        		// TODO : Edit config rows
        		
        		
        		return array('message' => 'Insert with success');
        	}
        }
        
        return array('message' => 'Insert fail');
    }
}
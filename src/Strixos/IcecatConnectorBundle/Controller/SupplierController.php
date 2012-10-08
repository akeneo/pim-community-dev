<?php

namespace Strixos\IcecatConnectorBundle\Controller;

use Strixos\DataFlowBundle\Model\Extract\FileUnzip;
use Strixos\DataFlowBundle\Model\Extract\FileHttpDownload;

use Strixos\IcecatConnectorBundle\Entity\Supplier;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use APY\DataGridBundle\Grid\Source\Entity as GridEntity;

use \XMLReader;

/**
 *
 * @author    Romain Monceau @ Akeneo
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class SupplierController extends Controller
{
    // TODO : put in configuration file
    const URL_SUPPLIERS = 'https://data.icecat.biz/export/freexml/refs/SuppliersList.xml.gz';
    const TMP_FILEPATH_SUPPLIERS = '/tmp/SuppliersList.xml.gz';
    const TMP_UNZIP_FILEPATH_SUPPLIERS = '/tmp/SuppliersList.xml';
    const AUTH_LOGIN = 'NicolasDupont';
    const AUTH_PASSWORD = '1cec4t**)';
    
    
    /**
     * @Route("/supplier/load-from-icecat")
     * @Template()
     */
    public function loadFromIcecatAction()
    {
        // -1- Download suppliers list in /tmp/...
        try {
        	$downloader = new FileHttpDownload();
        	$downloader->process(self::URL_SUPPLIERS, self::TMP_FILEPATH_SUPPLIERS, self::AUTH_LOGIN, self::AUTH_PASSWORD);
        } catch (\Exception $e) {
        	echo $e->getMessage();
        }
        
        // -2- Unzip file
        $unzipper = new FileUnzip();
        $this->process(self::TMP_FILEPATH_SUPPLIERS, self::TMP_UNZIP_FILEPATH_SUPPLIERS);
        
        // -3- Read xml document and parse to entities (suppliers)
        $xml = new XMLReader();
        $xml->open(self::TMP_UNZIP_FILEPATH_SUPPLIERS);
        
        $em = $this->getDoctrine()->getEntityManager();
        
        while ($xml->read())
        {
        	if ($xml->name === 'Supplier') {
        		$supplier = new Supplier();
        		$supplier->setIcecatId($xml->getAttribute('ID'));
        		$supplier->setName($xml->getAttribute('Name'));
        		$em->persist($supplier);
        	} else if ($xml->name === 'Response') {
        		$date = $xml->getAttribute('Date');
        	}
        }
        
        
        // -4- Save all entities in database
        $em->flush();
        
        return array();
    }
    
    /**
     * List Icecat suppliers in a grid
     * @Route("/supplier/list")
     * @Template()
     */
    public function listAction()
    {
    	// creates simple grid based on entity (ORM)
        $source = new GridEntity('StrixosIcecatConnectorBundle:Supplier');
        // get a grid instance
        $grid = $this->get('grid');
        // attach the source to the grid
        $grid->setSource($source);
        // add an action column
//         $rowAction = new RowAction('Import products', 'strixos_icecatconnector_default_loadproducts');
//         $rowAction->setRouteParameters(array('supplierId'));
//         $grid->addRowAction($rowAction);
        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('StrixosIcecatConnectorBundle:Supplier:grid.html.twig');
    }
}
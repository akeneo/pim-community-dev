<?php

namespace Strixos\IcecatConnectorBundle\Controller;

use Strixos\DataFlowBundle\Model\Extract\FileUnzip;
use Strixos\DataFlowBundle\Model\Extract\FileHttpDownload;

use Strixos\IcecatConnectorBundle\Entity\Product;

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
class ProductController extends Controller
{
    // TODO : put in configuration file
    const URL_PRODUCT = 'https://data.icecat.biz/export/freexml/product_mapping.xml';
    const TMP_FILEPATH_PRODUCTS = '/tmp/product_mapping.xml';
    const AUTH_LOGIN = 'NicolasDupont';
    const AUTH_PASSWORD = '1cec4t**)';
    
    
    /**
     * @Route("/product/load-from-icecat")
     * @Template()
     */
    public function loadFromIcecatAction()
    {
        // -1- Download suppliers list in /tmp/...
        try {
        	$downloader = new FileHttpDownload();
        	$downloader->process(self::URL_PRODUCT, self::TMP_FILEPATH_PRODUCTS, self::AUTH_LOGIN, self::AUTH_PASSWORD);
        } catch (\Exception $e) {
        	echo $e->getMessage();
        }
        
        // -3- Read xml document and parse to entities (suppliers)
        $xml = new XMLReader();
        $xml->open(self::TMP_FILEPATH_PRODUCTS);
        
        $suppliersList = array();
        $em = $this->getDoctrine()->getEntityManager();
        
        while ($xml->read())
        {
        	if ($xml->name === 'ProductMapping') {
        		$product = new Product();
        		$product->setProductId($xml->getAttribute('product_id'));
        		$product->setProdId($xml->getAttribute('prod_id'));
        		$product->setMProdId($xml->getAttribute('m_prod_id'));
        		$product->setSupplierId($xml->getAttribute('supplier_id'));
        		$em->persist($product);
        	} else if ($xml->name === 'Response') {
        		$date = $xml->getAttribute('Date');
        	}
        }
        
        // -4- Save all entities in database
        $em->flush();
        
        //var_dump($em);
        return array();
    }
    
    /**
     * List Icecat products in a grid
     * @Route("/product/list")
     * @Template()
     */
    public function listAction()
    {
    	// creates simple grid based on entity (ORM)
        $source = new GridEntity('StrixosIcecatConnectorBundle:Product');
        // get a grid instance
        $grid = $this->get('grid');
        // attach the source to the grid
        $grid->setSource($source);
        // add an action column
//         $rowAction = new RowAction('Import products', 'strixos_icecatconnector_default_loadproducts');
//         $rowAction->setRouteParameters(array('supplierId'));
//         $grid->addRowAction($rowAction);
        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('StrixosIcecatConnectorBundle:Product:grid.html.twig');
    }
}
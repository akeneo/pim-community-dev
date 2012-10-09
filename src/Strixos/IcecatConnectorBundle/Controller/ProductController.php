<?php

namespace Strixos\IcecatConnectorBundle\Controller;

use Strixos\IcecatConnectorBundle\Model\BaseExtractor;

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
        try {
            $em = $this->getDoctrine()->getEntityManager();
            $baseExtractor = new BaseExtractor($em);
            $baseExtractor->extractAndImportProductData();
        } catch (\Exception $e) {
            return array('exception' => $e);
        }
        
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
        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('StrixosIcecatConnectorBundle:Product:grid.html.twig');
    }
}
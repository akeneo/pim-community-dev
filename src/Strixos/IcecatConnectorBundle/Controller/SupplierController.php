<?php

namespace Strixos\IcecatConnectorBundle\Controller;

use Strixos\DataFlowBundle\Model\Extract\FileUnzip;
use Strixos\DataFlowBundle\Model\Extract\FileHttpDownload;
use Strixos\IcecatConnectorBundle\Model\Import\SupplierImportDataFromXml;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use APY\DataGridBundle\Grid\Source\Entity as GridEntity;

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
        
            // -2- Unzip file
            $unzipper = new FileUnzip();
            $unzipper->process(self::TMP_FILEPATH_SUPPLIERS, self::TMP_UNZIP_FILEPATH_SUPPLIERS);
        
            // -3- Call XML Loader to save in database
            $em = $this->getDoctrine()->getEntityManager();
            $loader = new SupplierImportDataFromXml($em);
            $loader->process(self::TMP_UNZIP_FILEPATH_SUPPLIERS);
        } catch (\Exception $e) {
            return array('exception' => $e);
        }
        
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
        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('StrixosIcecatConnectorBundle:Supplier:grid.html.twig');
    }
}
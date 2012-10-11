<?php
namespace Strixos\IcecatConnectorBundle\Controller;

use Strixos\IcecatConnectorBundle\Model\BaseExtractor;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Action\RowAction;

use Strixos\IcecatConnectorBundle\Model\ProductLoader;
use Akeneo\CatalogBundle\Model\BaseFieldFactory;

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

        return $this->redirect($this->generateUrl('strixos_icecatconnector_product_list'));
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
        $grid = $this->get('grid');
        $grid->setSource($source);
        // add an action column to load import of all products of a supplier
        $rowAction = new RowAction('Import product to PIM', 'strixos_icecatconnector_product_loadproducts');
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);
        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('StrixosIcecatConnectorBundle:Product:grid.html.twig');
    }

    /**
     * List Icecat suppliers in a grid
     * @Route("/product/load-products/{id}")
     * @Template()
     */
    public function loadProductsAction($id)
    {
        // define values
        $prodId = 'RJ459AV';
        $supplierName = 'hp';
        $locale = 'fr';
        
        try {
            $em = $this->getDoctrine()->getEntityManager();
            $baseExtractor = new BaseExtractor($em);
            $baseExtractor->extractAndImportProduct($prodId, $supplierName, $locale);
        } catch (Exception $e) {
            return array('exception' => $e);
        }
        // TODO move this stuff in custom model operation

        // get for supplier = 1 there are lot of data
        //$prodId = 'D9194B';

        

        // TODO: mark as already imported in product table with pim product id so the second time we can load existing
        // product with find an updated it not re-create (as for type)

        return new Response('Load detailled data.');
    }
}
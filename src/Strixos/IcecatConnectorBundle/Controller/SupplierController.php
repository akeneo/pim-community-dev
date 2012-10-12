<?php
namespace Strixos\IcecatConnectorBundle\Controller;

use Strixos\IcecatConnectorBundle\Model\BaseExtractor;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Action\RowAction;

/**
 *
 * @author    Romain Monceau @ Akeneo
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class SupplierController extends Controller
{
    /**
     * @Route("/supplier/load-from-icecat")
     * @Template()
     */
    public function loadFromIcecatAction()
    {
        try {
        	
        	$srvConnector = $this->container->get('akeneo.icecatconnector_service');
        	$srvConnector->importSuppliers();
        	
        } catch (\Exception $e) {
            return array('exception' => $e);
        }

        return $this->redirect($this->generateUrl('strixos_icecatconnector_supplier_list'));
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
        $grid = $this->get('grid');
        $grid->setSource($source);
        // add an action column to load import of all products of a supplier
        $rowAction = new RowAction('Import products to PIM', 'strixos_icecatconnector_supplier_loadproducts');
        $rowAction->setRouteParameters(array('icecatId'));
        $grid->addRowAction($rowAction);
        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('StrixosIcecatConnectorBundle:Supplier:grid.html.twig');
    }

    /**
     * List Icecat suppliers in a grid
     * @Route("/supplier/load-products/{icecatId}")
     * @Template()
     */
    public function loadProductsAction($icecatId)
    {
        $icecatSupplierId = 2;
        $locale = 'fr';

        // get all products for supplier id requested
        $em = $this->getDoctrine()->getEntityManager();
        $supplier = $em->getRepository('StrixosIcecatConnectorBundle:Supplier')->findOneByIcecatId($icecatSupplierId);
        $products = $em->getRepository('StrixosIcecatConnectorBundle:Product')->findBySupplier($supplier);

        $baseExtractor = new BaseExtractor($em);
        foreach ($products as $product) {
            $baseExtractor->extractAndImportProduct($product->getProdId(), $supplier->getName(), $locale);
        }


        echo 'ID : '. $supplier->getId() .'<br />';
        echo 'Name : '. $supplier->getName() .'<br />';
        echo 'Icecat Id: '. $supplier->getIcecatId() .'<br />';
        echo 'Count : '. count($products) .'<br />';

        //return array();


        die('load all products for supplier id '.$icecatId);
        // TODO load any product of this supplier
    }
}
<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Controller;

use Pim\Bundle\ConnectorIcecatBundle\Service\ConnectorService;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Action\RowAction;

use \Exception;
use Doctrine\DBAL\DBALException;
/**
 * Icecat supplier controller regroups all features for suppliers entities (import, list and import linked products)
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/supplier")
 */
class SupplierController extends Controller
{
    /**
     * Load suppliers from icecat to local database
     *
     * @return Response
     *
     * @Route("/load-from-icecat")
     * @Template()
     */
    public function loadFromIcecatAction()
    {
        try {
            $srvConnector = $this->getConnectorService();
            $srvConnector->importIcecatSuppliers();
            $this->get('session')->setFlash('success', 'Base suppliers has been imported from Icecat');
        } catch (DBALException $e) {
            $this->container->get('logger')->err($e->getCode() .' : '. $e->getMessage());
            $this->get('session')->setFlash('exception', 'Erreur en base de données lors de l\'import');
        } catch (Exception $e) {
            $this->get('session')->setFlash('exception', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('pim_connectoricecat_supplier_list'));
    }

    /**
     * List Icecat suppliers in a grid
     *
     * @return Response
     *
     * @Route("/list")
     * @Template()
     */
    public function listAction()
    {
        // creates simple grid based on entity (ORM)
        $source = new GridEntity('PimConnectorIcecatBundle:SourceSupplier');
        $grid = $this->get('grid');
        $grid->setSource($source);
        // add an action column to load import of all products of a supplier
        $rowAction = new RowAction('Import products to PIM', 'pim_connectoricecat_supplier_loadproducts');
        $rowAction->setRouteParameters(array('icecatId'));
        $grid->addRowAction($rowAction);
        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('PimConnectorIcecatBundle:Supplier:grid.html.twig');
    }

    /**
     * Load all product linked to a defined supplier
     *
     * @param integer $icecatId
     *
     * @return Response
     *
     * @Route("/{icecatId}/load-products")
     * @Template()
     */
    public function loadProductsAction($icecatId)
    {
        try {
            // get supplier from icecat id
            $supplier = $this->getDoctrine()
                             ->getRepository('PimConnectorIcecatBundle:SourceSupplier')
                             ->findOneByIcecatId($icecatId);
            $srvConnector = $this->getConnectorService();
            $srvConnector->importProductsFromSupplier($supplier);

            // Get supplier products
            $products = $this->getDoctrine()->getRepository('PimConnectorIcecatBundle:SourceProduct')
                    ->findBySupplier($supplier);

            // Prepare success message
            $viewRenderer = $this->render('PimConnectorIcecatBundle:Supplier:loadProducts.html.twig',
                array('supplier' => $supplier, 'products' => $products)
            );
            $this->get('session')->setFlash('success', $viewRenderer->getContent());
        } catch (Exception $e) {
            $this->get('session')->setFlash('error', $e->getMessage());
        }

        // Redirect to suppliers list
        return $this->redirect($this->generateUrl('pim_connectoricecat_supplier_list'));
    }

    /**
     * @return ConnectorService
     */
    protected function getConnectorService()
    {
        return $this->container->get('pim.connector_icecat.icecat_service');
    }
}

<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use APY\DataGridBundle\Grid\Source\Document as GridSource;
use APY\DataGridBundle\Grid\Action\RowAction;

use \Exception;
use Doctrine\DBAL\DBALException;
/**
 * Icecat product controller regroups all features for products entities (import and list)
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/product")
 */
class ProductController extends Controller
{
    /**
     * Load only products identifiers from icecat to local database
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
            $srvConnector->importIcecatBaseProducts();
            $this->get('session')->setFlash('success', 'Base products has been imported from Icecat');
        } catch (DBALException $e) {
            $this->container->get('logger')->err($e->getCode() .' : '. $e->getMessage());
            $this->get('session')->setFlash('error', 'Erreur en base de données lors de l\'import');
        } catch (Exception $e) {
            $this->get('session')->setFlash('error', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('pim_connectoricecat_product_list'));
    }

    /**
     * List Icecat products in a grid
     *
     * @return Response
     *
     * @Route("/list")
     * @Template()
     */
    public function listAction()
    {
        // creates simple grid based on entity (ORM)
        $source = new GridSource('PimConnectorIcecatBundle:IcecatProductDataSheet');
        $grid = $this->get('grid');
        $grid->setSource($source);
        // add an action column to load import of all datas of the product
        //$rowAction = new RowAction('Import product to PIM', 'pim_connectoricecat_product_loadproduct');
        //$rowAction->setRouteParameters(array('id'));
        //$grid->addRowAction($rowAction);
        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('PimConnectorIcecatBundle:Product:grid.html.twig');
    }

    /**
     * Load all icecat product data to local database
     *
     * @param integer $productId
     *
     * @return Response
     *
     * @Route("/{productId}/load-product")
     * @Template()
     */
    public function loadProductAction($productId)
    {
        try {
            $srvConnector = $this->getConnectorService();
            $srvConnector->importProductFromIcecatXml($productId);
            $this->get('session')->setFlash('success', 'Icecat datasheet has been imported as product');
        } catch (Exception $e) {
            $this->get('session')->setFlash('error', $e->getMessage());
        }

        // Redirect to products list
        return $this->redirect($this->generateUrl('pim_connectoricecat_product_list'));
    }

    /**
     * @return ConnectorService
     */
    protected function getConnectorService()
    {
        return $this->container->get('pim.connector_icecat.icecat_service');
    }
}

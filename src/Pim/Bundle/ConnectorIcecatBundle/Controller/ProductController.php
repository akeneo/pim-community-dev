<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Action\RowAction;

use \Exception;
use Doctrine\DBAL\DBALException;
/**
 * Icecat product controller regroups all features for products entities (import and list)
 *
 * @author    Romain Monceau @ Akeneo
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductController extends Controller
{
    /**
     * Load only products identifiers from icecat to local database
     *
     * @Route("/product/load-from-icecat")
     * @Template()
     */
    public function loadFromIcecatAction()
    {
        try {
            $srvConnector = $this->container->get('akeneo.connector.icecat_service');
            $srvConnector->importProducts();
            $this->get('session')->setFlash('notice', 'Base products has been imported from Icecat');
        } catch (DBALException $e) {
            $this->container->get('logger')->err($e->getCode() .' : '. $e->getMessage());
            $this->get('session')->setFlash('exception', 'Erreur en base de données lors de l\'import');
        } catch (Exception $e) {
            $this->get('session')->setFlash('exception', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('pim_connectoricecat_product_list'));
    }

    /**
     * List Icecat products in a grid
     *
     * @Route("/product/list")
     * @Template()
     */
    public function listAction()
    {
        // creates simple grid based on entity (ORM)
        $source = new GridEntity('PimConnectorIcecatBundle:SourceProduct');
        $grid = $this->get('grid');
        $grid->setSource($source);
        // add an action column to load import of all datas of the product
        $rowAction = new RowAction('Import product to PIM', 'pim_connectoricecat_product_loadproduct');
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);
        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('PimConnectorIcecatBundle:Product:grid.html.twig');
    }

    /**
     * Load all icecat product data to local database
     *
     * @Route("/product/{id}/load-product")
     * @Template()
     */
    public function loadProductAction($id)
    {
        try {
            $srvConnector = $this->container->get('akeneo.connector.icecat_service');
            $srvConnector->importProductFromIcecatXml($id);
            $product = $this->getDoctrine()->getRepository('PimConnectorIcecatBundle:SourceProduct')->find($id);

            // Prepare notice message
            $viewRenderer = $this->render('PimConnectorIcecatBundle:Product:loadProduct.html.twig',
                    array('product' => $product));
            $this->get('session')->setFlash('notice', $viewRenderer->getContent());
        } catch (Exception $e) {
            $this->get('session')->setFlash('exception', $e->getMessage());
        }

        // Redirect to products list
        return $this->redirect($this->generateUrl('pim_connectoricecat_product_list'));
    }
}

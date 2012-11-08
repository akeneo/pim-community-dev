<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Pim\Bundle\CatalogBundle\Form\Type\GroupType;

use Pim\Bundle\CatalogBundle\Form\Type\ProductTypeType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Source\Document as GridDocument;
use APY\DataGridBundle\Grid\Action\RowAction;

use Pim\Bundle\CatalogBundle\Document\ProductTypeMongo;

use \Exception;
/**
 * Product type controller.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/producttype")
 */
class ProductTypeController extends Controller
{

    /**
     * @return ProductManager
     */
    public function getProductManager()
    {
        return $this->get('pim.catalog.product_manager');
    }

    /**
     * @return ObjectManager
     */
    public function getPersistenceManager()
    {
        return $this->getProductManager()->getPersistenceManager();
    }

    /**
     * Return grid source for APY grid
     * @return APY\DataGridBundle\Grid\Source\Entity
    */
    public function getGridSource($shortName)
    {
        // source to create simple grid based on entity or document (ORM or ODM)
        if ($this->getPersistenceManager() instanceof \Doctrine\ODM\MongoDB\DocumentManager) {
            return new GridDocument($shortName);
        } else if ($this->getPersistenceManager() instanceof \Doctrine\ORM\EntityManager) {
            return new GridEntity($shortName);
        } else {
            throw new \Exception('Unknow object manager');
        }
    }

    /**
     * Lists all fields
     *
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        $productManager = $this->getProductManager();

        // creates simple grid based on entity or document (ORM or ODM)
        $source = $this->getGridSource($productManager->getFieldShortname());

        $grid = $this->get('grid');
        $grid->setSource($source);

        // add action columns
        $grid->setActionsColumnSeparator('&nbsp;');
        $rowAction = new RowAction('Edit', 'pim_catalog_productfield_edit', false, '_self', array('class' => 'grid_action ui-icon-fugue-tag--pencil'));
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);

        $rowAction = new RowAction('Delete', 'pim_catalog_productfield_delete', true, '_self', array('class' => 'grid_action ui-icon-fugue-tag--minus'));
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);

        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('PimCatalogBundle:ProductType:index.html.twig');
    }

}
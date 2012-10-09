<?php
namespace Akeneo\CatalogBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Action\RowAction as GridRowAction;

/**
 * Field controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class FieldController extends Controller
{
    /**
     * @Route("/field/index")
     * @Template()
     */
    public function indexAction()
    {
        // creates simple grid based on entity (ORM)
        $source = new GridEntity('Akeneo\CatalogBundle\Entity\Product\Field');
        // get a grid instance
        $grid = $this->get('grid');
        // attach the source to the grid
        $grid->setSource($source);
        // add an action column
         $rowAction = new GridRowAction('Edit', 'akeneo_catalog_field_index');
         $rowAction->setRouteParameters(array('id'));
         $grid->addRowAction($rowAction);
        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('AkeneoCatalogBundle:Field:index.html.twig');
    }

    /**
     * @Route("/field/new")
     * @Template()
     */
    public function newAction()
    {
    }

}

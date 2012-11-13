<?php
namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\CatalogBundle\Form\Type\ProductAttributeType;
use Pim\Bundle\UIBundle\Grid\Helper as GridHelper;
use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Column\TextColumn;
use APY\DataGridBundle\Grid\Export\ExcelExport;
use APY\DataGridBundle\Grid\Export\CSVExport;
use Symfony\Component\Form\Form;

/**
 * Product attribute controller.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/productattribute")
 */
class ProductAttributeController extends Controller
{

    /**
     * @return ProductManager
     */
    protected function getProductManager()
    {
        return $this->get('pim.catalog.product_manager');
    }

    /**
     * @return DocumentManager
     */
    protected function getPersistenceManager()
    {
        return $this->getProductManager()->getPersistenceManager();
    }

    /**
     * Lists all attributes
     *
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        // creates simple grid based on entity or document (ORM or ODM)
        $source = GridHelper::getGridSource($this->getPersistenceManager(), $this->getProductManager()->getAttributeShortname());
        $grid = $this->get('grid');
        $grid->setSource($source);
        $grid->addExport(new ExcelExport('Excel Export'));
        $grid->addExport(new CSVExport('CSV Export'));

        // add action columns
        $grid->setActionsColumnSeparator('&nbsp;');
        $rowAction = new RowAction('Edit', 'pim_catalog_productattribute_edit', false, '_self', array('class' => 'grid_action ui-icon-fugue-tag--pencil'));
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);

        $rowAction = new RowAction('Delete', 'pim_catalog_productattribute_delete', true, '_self', array('class' => 'grid_action ui-icon-fugue-tag--minus'));
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);

        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('PimCatalogBundle:ProductAttribute:index.html.twig');
    }

    /**
     * Create attribute form
     *
     * @param ProductAttribute $attribute
     * @return Form
     */
    protected function createAttributeForm($attribute)
    {
        $attClassFullName = $this->getProductManager()->getAttributeClass();
        $optClassFullName = $this->getProductManager()->getAttributeOptionClass();
        $form = $this->createForm(new ProductAttributeType($attClassFullName, $optClassFullName), $attribute);
        return $form;
    }

    /**
     * Displays a form to create a new attribute
     *
     * @Route("/new")
     * @Template()
     */
    public function newAction()
    {
        $instance = $this->getProductManager()->getNewAttributeInstance();
        $form = $this->createAttributeForm($instance);

        // render form
        return $this->render(
            'PimCatalogBundle:ProductAttribute:new.html.twig',
            array('entity' => $instance, 'form' => $form->createView())
        );
    }

    /**
     * Creates a new attribute
     *
     * @Route("/create")
     * @Method("POST")
     * @Template("PimCatalogBundle:ProductAttribute:edit.html.twig")
     */
    public function createAction(Request $request)
    {
        $instance = $this->getProductManager()->getNewAttributeInstance();
        $form = $this->createAttributeForm($instance);
        $form->bind($request);

        // TODO : avoid to create product attribute with same code -> complete validation !
        if ($form->isValid()) {
            $manager = $this->getPersistenceManager();

            try {
                $manager->persist($instance);
                $manager->flush();
                $this->get('session')->setFlash('success', "Attribute {$instance->getCode()} has been created");

                return $this->redirect(
                    $this->generateUrl('pim_catalog_productattribute_edit', array('id' => $instance->getId()))
                );
            } catch (\Exception $e) {
                $this->get('session')->setFlash('error', $e->getMessage());
            }
        }

        // render form with errors
        return $this->render(
            'PimCatalogBundle:ProductAttribute:new.html.twig',
            array('entity' => $instance, 'form' => $form->createView())
        );
    }

    /**
     * Displays a form to edit an existing attribute entity.
     *
     * @Route("/{id}/edit")
     * @Template()
     */
    public function editAction($id)
    {
        $instance = $this->getProductManager()->getAttributeRepository()->find($id);

        if (!$instance) {
            throw $this->createNotFoundException('Unable to find product attribute.');
        }

        $form = $this->createAttributeForm($instance);

        // render form
        return $this->render(
            'PimCatalogBundle:ProductAttribute:edit.html.twig',
            array('entity' => $instance, 'form' => $form->createView())
        );
    }

    /**
     * Edits an existing attribute entity.
     *
     * @Route("/{id}/update")
     * @Method("POST")
     * @Template("PimCatalogBundle:ProductAttribute:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $instance = $this->getProductManager()->getAttributeRepository()->find($id);

        if (!$instance) {
            throw $this->createNotFoundException('Unable to find product attribute.');
        }

        $form = $this->createAttributeForm($instance);
        $form->bind($request);

        if ($form->isValid()) {

            try {
                $manager = $this->getPersistenceManager();
                $manager->persist($instance);
                $manager->flush();
                $this->get('session')->setFlash('success', "Attribute {$instance->getCode()} has been updated");

                return $this->redirect($this->generateUrl('pim_catalog_productattribute_edit', array('id' => $id)));

            } catch (\Exception $e) {
                $this->get('session')->setFlash('error', $e->getMessage());
            }

        }

        // render form with error
        return $this->render(
            'PimCatalogBundle:ProductAttribute:edit.html.twig',
            array('entity' => $instance, 'form' => $form->createView())
        );
    }

    /**
     * Deletes a ProductAttribute entity.
     *
     * @Route("/{id}/delete")
     */
    public function deleteAction(Request $request, $id)
    {
        $instance = $this->getProductManager()->getAttributeRepository()->find($id);

        if (!$instance) {
            throw $this->createNotFoundException('Unable to find product attribute.');
        }

        $attributeCode = $instance->getCode();
        $manager = $this->getPersistenceManager();
        $manager->remove($instance);
        $manager->flush();

        $this->get('session')->setFlash('success', "Attribute {$attributeCode} has been deleted");

        return $this->redirect($this->generateUrl('pim_catalog_productattribute_index'));
    }

}

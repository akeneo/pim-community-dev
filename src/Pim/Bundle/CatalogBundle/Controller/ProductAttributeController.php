<?php
namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Document\ProductAttributeMongo;
use Pim\Bundle\CatalogBundle\Form\Type\ProductAttributeType;
use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Source\Document as GridDocument;
use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Column\TextColumn;
use APY\DataGridBundle\Grid\Export\ExcelExport;
use APY\DataGridBundle\Grid\Export\CSVExport;

/**
 * Product attribute controller.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/productattribute")
 */
class ProductAttributeController extends AbstractProductController
{
    /**
     * (non-PHPdoc)
     * @see Parent
     */
    public function getObjectShortName()
    {
        return $this->get('pim.catalog.product_manager')->getAttributeShortname();
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
        $source = $this->getGridSource();
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
     * Displays a form to create a new attribute
     *
     * @Route("/new")
     * @Template()
     */
    public function newAction()
    {
        $entity = $this->getNewObject();
        $classFullName = $this->get('pim.catalog.product_manager')->getAttributeClass();
        $form = $this->createForm(new ProductAttributeType($classFullName), $entity);

        // render form
        return $this->render(
            'PimCatalogBundle:ProductAttribute:new.html.twig', array('entity' => $entity, 'form' => $form->createView())
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
        $entity  = $this->getNewObject();
        $classFullName = $this->get('pim.catalog.product_manager')->getAttributeClass();

        $form = $this->createForm(new ProductAttributeType($classFullName), $entity);
        $form->bind($request);

        // TODO : avoid to create product attribute with same code -> complete validation !
        if ($form->isValid()) {
            $manager = $this->getObjectManagerService();
            $manager->persist($entity);
            $manager->flush();
            $this->get('session')->setFlash('success', 'Attribute has been created');

            return $this->redirect($this->generateUrl('pim_catalog_productattribute_edit', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
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
        $manager = $this->getObjectManagerService();

        $entity = $manager->getRepository($this->getObjectShortName())->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find product attribute.');
        }

        $classFullName = $this->get('pim.catalog.product_manager')->getAttributeClass();
        $editForm = $this->createForm(new ProductAttributeType($classFullName), $entity);

        $params = array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
        );

        // render form
        return $this->render('PimCatalogBundle:ProductAttribute:edit.html.twig', $params);
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
        $manager = $this->getObjectManagerService();

        $entity = $manager->getRepository($this->getObjectShortName())->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find product attribute.');
        }

        $classFullName = $this->get('pim.catalog.product_manager')->getAttributeClass();
        $editForm = $this->createForm(new ProductAttributeType($classFullName), $entity);
        $editForm->bind($request);

        /*
        // sort options
        $postData = $request->get('pim_catalogbundle_ProductAttributetype');
        if (isset($postData['options']) and count($postData['options']) > 0) {
            $indOption = 1;
            foreach ($postData['options'] as &$option) {
                $option['sortOrder']= $indOption++;
            }
        }




        var_dump($postData['options']);

        foreach ($entity->getOptions() as $opt) {
            var_dump($opt);
        }

        exit();
	*/



        if ($editForm->isValid()) {

            // set option order



            $manager->persist($entity);
            $manager->flush();
            $this->get('session')->setFlash('success', 'Attribute has been updated');

            return $this->redirect($this->generateUrl('pim_catalog_productattribute_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        );
    }

    /**
     * Deletes a ProductAttribute entity.
     *
     * @Route("/{id}/delete")
     */
    public function deleteAction(Request $request, $id)
    {
        $manager = $this->getObjectManagerService();
        $entity = $manager->getRepository($this->getObjectShortName())->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find product attribute.');
        }

        $manager->remove($entity);
        $manager->flush();

        $this->get('session')->setFlash('success', 'Attribute has been deleted');

        return $this->redirect($this->generateUrl('pim_catalog_productattribute_index'));
    }

}

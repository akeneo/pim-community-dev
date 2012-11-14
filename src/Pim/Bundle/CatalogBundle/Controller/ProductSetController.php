<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;

use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;

use Pim\Bundle\CatalogBundle\Form\Type\ProductSetType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Source\Document as GridDocument;
use APY\DataGridBundle\Grid\Action\RowAction;
use Pim\Bundle\UIBundle\Grid\Helper as GridHelper;

use \Exception;
/**
 * Product set controller.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/productset")
 */
class ProductSetController extends Controller
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
     * Create set form
     *
     * @param ProductSet $set
     * @return Form
     */
    protected function createSetForm($set)
    {
        $setClass = $this->getProductManager()->getSetClass();
        $groupClass = $this->getProductManager()->getGroupClass();
        $attClass = $this->getProductManager()->getAttributeClass();
        $formType = new ProductSetType(
            $setClass, $groupClass, $attClass, $this->getCopySetOptions(), $this->getAvailableAttributes($set)
        );
        $form = $this->createForm($formType, $set);
        return $form;
    }

    /**
     * Lists all sets
     *
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        $productManager = $this->getProductManager();

        // creates simple grid based on entity or document (ORM or ODM)
        $source = GridHelper::getGridSource($this->getPersistenceManager(), $this->getProductManager()->getSetShortname());

        $grid = $this->get('grid');
        $grid->setSource($source);

        // add action columns
        $grid->setActionsColumnSeparator('&nbsp;');
        $rowAction = new RowAction('Edit', 'pim_catalog_productset_edit', false, '_self', array('class' => 'grid_action ui-icon-fugue-folder--pencil'));
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);

        $rowAction = new RowAction('Delete', 'pim_catalog_productset_delete', true, '_self', array('class' => 'grid_action ui-icon-fugue-folder--minus'));
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);

        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('PimCatalogBundle:ProductSet:index.html.twig');
    }

    /**
     * @Route("/new")
     * @Template()
     */
    public function newAction()
    {
        return $this->forward('PimCatalogBundle:ProductSet:create');
    }

    /**
     *
     * @param Request $request
     *
     * @Route("/create")
     * @Template()
     */
    public function createAction(Request $request = null)
    {
        // create new product set
        $productManager = $this->getProductManager();
        $entity = $productManager->getNewSetInstance();

        // prepare form
        $form = $this->createSetForm($entity);

        if ($request && $request->isMethod('POST')) {
            $form->bind($request);
            $postData = $request->get('pim_catalogbundle_productattributeset');

            // TODO : Must be in validation form
            if ($form->isValid() && isset($postData['copyfromset'])) {

                $copy = $postData['copyfromset'];

                if ($copy !== '') { // create by copy
                    $productType = $this->getProductManager()->getSetRepository()->find($postData['copyfromset']);
                    $entity = $this->getProductManager()->cloneSet($productType);
                    $entity->setCode($postData['code']);
                }

                // persist
                $this->getPersistenceManager()->persist($entity);
                $this->getPersistenceManager()->flush();

                $this->get('session')->setFlash('success', 'product set has been created');

                // TODO : redirect to edit
                return $this->redirect(
                        $this->generateUrl('pim_catalog_productset_edit', array('id' => $entity->getId()))
                );
            }
        }

        return $this->render('PimCatalogBundle:ProductSet:new.html.twig', array('form' => $form->createView()));
    }

    /**
     *
     * @param integer $id
     *
     * @Route("/{id}/edit")
     * @Template()
     */
    public function editAction($id)
    {
        $entity = $this->getProductManager()->getSetRepository()->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No product set found for id '. $id);
        }

        // prepare & render form
        $form = $this->createSetForm($entity);
        return $this->render('PimCatalogBundle:ProductSet:edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
    }

    /**
     *
     * @param Request $request
     *
    * @Route("/{id}/update")
     * @Template()
     */
    public function updateAction(Request $request, $id)
    {
        if ($request->isMethod('POST')) {
            // get product set
            $postData = $request->get('pim_catalogbundle_productattributeset');
            var_dump($postData);
//             exit;

            $id = isset($postData['id']) ? $postData['id'] : false;
            $entity = $this->getProductManager()->getSetRepository()->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('No product set found for id '. $id);
            }


            $form = $this->createSetForm($entity);



            $form->bind($request);
            foreach ($entity->getGroups() as $group) {
                var_dump($group->getAttributes()->count());
            }
//             exit;
            if ($form->isValid()) {
                $this->getPersistenceManager()->persist($entity);
                $this->getPersistenceManager()->flush();

                $this->get('session')->setFlash('success', 'product set has been saved');
            }

            return $this->render('PimCatalogBundle:ProductSet:edit.html.twig', array('form' => $form->createView()));

        } else {
            $this->get('session')->setFlash('notice', 'Incorrect update product set call');
            return $this->redirect($this->generateUrl('pim_catalog_productset_index'));
        }
    }

    /**
     * Remove an entity
     *
     * @param integer $id
     *
     * @Route("/{id}/delete")
     * @Template()
     *
     * TODO : Must prevent against incorrect id
     * TODO : Just a flag to disable entity without physically remove
     * TODO : Add form and verify it.. CSRF fault
     */
    public function deleteAction($id)
    {
        $entity = $this->getProductManager()->getSetRepository()->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No product set found for id '. $id);
        }

        $this->getPersistenceManager()->remove($entity);
        $this->getPersistenceManager()->flush();

        $this->get('session')->setFlash('success', 'product has been removed');

        return $this->redirect(
                $this->generateUrl('pim_catalog_productset_index')
        );
    }

    /**
     * Get attributes
     * @return ArrayCollection
     * TODO : must be move in custom repository storage agnostic
     */
    protected function getAvailableAttributes($set)
    {
        $repo = $this->getProductManager()->getAttributeRepository();
        return $repo->findAllExcept($set);
    }

    /**
     * @return array
     */
    private function getCopySetOptions()
    {
        $sets = $this->getProductManager()->getSetRepository()->findAll();
        $setIdToName = array();
        foreach ($sets as $set) {
            $setIdToName[$set->getId()]= $set->getCode();
        }
        return $setIdToName;
    }
}
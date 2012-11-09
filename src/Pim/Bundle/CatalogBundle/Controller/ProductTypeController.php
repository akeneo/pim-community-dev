<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ODM\MongoDB\DocumentManager;

use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;

use Pim\Bundle\CatalogBundle\Form\Type\ProductTypeType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Source\Document as GridDocument;
use APY\DataGridBundle\Grid\Action\RowAction;

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
        $source = $this->getGridSource($productManager->getTypeShortname());

        $grid = $this->get('grid');
        $grid->setSource($source);

        // add action columns
        $grid->setActionsColumnSeparator('&nbsp;');
        $rowAction = new RowAction('Edit', 'pim_catalog_producttype_edit', false, '_self', array('class' => 'grid_action ui-icon-fugue-tag--pencil'));
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);

        $rowAction = new RowAction('Delete', 'pim_catalog_producttype_delete', true, '_self', array('class' => 'grid_action ui-icon-fugue-tag--minus'));
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);

        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('PimCatalogBundle:ProductType:index.html.twig');
    }

    /**
     * @Route("/new")
     * @Template()
     */
    public function newAction()
    {
        return $this->forward('PimCatalogBundle:ProductType:create');
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
        // create new product type
        $productManager = $this->getProductManager();
        $entity = $productManager->getNewTypeInstance();
        
        // create type, set list of existing type to prepare copy list
        $type = new ProductTypeType();
        $type->setCopyTypeOptions($this->_getCopyTypeOptions());
        
        // prepare & render form
        $form = $this->createForm($type, $entity);
        
        if ($request && $request->isMethod('POST')) {
            $form->bind($request);
            $postData = $request->get('akeneo_catalog_producttype');
            
            // TODO : Must be in validation form
            if ($form->isValid() && isset($postData['copyfromset'])) {
                
                $copy = $postData['copyfromset'];
                
                if ($copy !== '') { // create by copy
                    $productType = $this->getProductManager()->getTypeRepository()->find($postData['copyfromset']);
                    $entity = $this->getProductManager()->cloneType($productType);
                    $entity->setCode($postData['code']);
                }
                
                // persist
                $this->getPersistenceManager()->persist($entity);
                $this->getPersistenceManager()->flush();
                
                $this->get('session')->setFlash('notice', 'product type has been saved');
                
                // TODO : redirect to edit
                return $this->redirect(
                        $this->generateUrl('pim_catalog_producttype_edit', array('id' => $entity->getId()))
                );
            }
        }
        
        return $this->render('PimCatalogBundle:ProductType:new.html.twig', array('form' => $form->createView()));
    }
    
    /**
     * 
     * @param integer $id
     * 
     * @Route("/edit/{id}")
     * @Template()
     */
    public function editAction($id)
    {
        $entity = $this->getProductManager()->getTypeRepository()->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No product type found for id '. $id);
        }
        
        $type = new ProductTypeType();
        $type->setAvailableFields($this->getAvailableFields());
        
        // prepare & render form
        $form = $this->createForm($type, $entity);
        return $this->render('PimCatalogBundle:ProductType:edit.html.twig', array('form' => $form->createView()));
    }
    
    /**
     * Get fields 
     * @return ArrayCollection
     * TODO : must be move in custom repository storage agnostic
     */
    protected function getAvailableFields()
    {
        $dm = $this->getPersistenceManager();
        $qb = $dm->createQueryBuilder($this->getProductManager()->getFieldShortname());
        $q = $qb->field('code')->notIn(array('binomed-att'))->getQuery();
        return $q->execute();
    }
    
    /**
     * 
     * @param Request $request
     * 
     * @Route("/update")
     * @Template()
     */
    public function updateAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            // get product type
            $postData = $request->get('akeneo_catalog_producttype');
//             var_dump($postData);
            
//             exit;
            
            $id = isset($postData['id']) ? $postData['id'] : false;
            $entity = $this->getProductManager()->getTypeRepository()->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('No product type found for id '. $id);
            }
            
            // 
            $type = new ProductTypeType();
            
            $form = $this->createForm($type, $entity);
            $form->bind($request);
            
            if ($form->isValid()) {
                $this->getPersistenceManager()->persist($entity);
                $this->getPersistenceManager()->flush();
                
                $this->get('session')->setFlash('notice', 'product type has been saved');
            }
            
            return $this->render('PimCatalogBundle:ProductType:edit.html.twig', array('form' => $form->createView()));
            
        } else {
            $this->get('session')->setFlash('notice', 'Incorrect update product type call');
            return $this->redirect($this->generateUrl('pim_catalog_producttype_index'));
        }
    }
    
    /**
     * Remove an entity
     * 
     * @param integer $id
     * 
     * @Route("/delete/{id}")
     * @Template()
     * 
     * TODO : Must prevent against incorrect id
     * TODO : Just a flag to disable entity without physically remove
     * TODO : Add form and verify it.. CSRF fault
     */
    public function deleteAction($id)
    {
        $entity = $this->getProductManager()->getTypeRepository()->find($id);
        
        if (!$entity) {
            throw $this->createNotFoundException('No product type found for id '. $id);
        }
        
        $this->getPersistenceManager()->remove($entity);
        $this->getPersistenceManager()->flush();
        
        $this->get('session')->setFlash('notice', 'product has been removed');
        
        return $this->redirect(
                $this->generateUrl('pim_catalog_producttype_index')
        );
    }

    /**
     * @return array
     */
    private function _getCopyTypeOptions()
    {
        $types = $this->getProductManager()->getTypeRepository()->findAll();
        $typeIdToName = array();
        foreach ($types as $type) {
            $typeIdToName[$type->getId()]= $type->getCode();
        }
        return $typeIdToName;
    }
}
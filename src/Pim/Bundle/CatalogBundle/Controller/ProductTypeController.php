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
     * TODO aims to easily change from one implementation to other
     */
    const DOCTRINE_MANAGER = 'doctrine.orm.entity_manager';
    const DOCTRINE_MONGO_MANAGER = 'doctrine.odm.mongodb.document_manager';
    protected $managerService = self::DOCTRINE_MONGO_MANAGER;
    protected $classShortname = 'PimCatalogBundle:ProductTypeMongo';

    /**
     * Lists all fields
     *
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('pim_catalog_producttype_list'));
    }
    
    /**
     * initialize manager
     * @throws Exception
     */
    protected function init()
    {
        $this->managerService = $this->get(self::DOCTRINE_MONGO_MANAGER);
    }
    
    /**
     * List all product types
     * 
     * @throws Exception
     * 
     * @Route("/list")
     * @Template()
     */
    public function listAction()
    {
        // creates simple grid based on entity or document (ORM or ODM)
        if ($this->managerService == self::DOCTRINE_MONGO_MANAGER) {
            $source = new GridDocument($this->classShortname);
        } else if ($this->managerService == self::DOCTRINE_MANAGER) {
            $source = new GridEntity($this->classShortname);
        } else {
            throw new Exception('Unknow object manager');
        }
        
        // create grid of product types
        $grid = $this->get('grid');
        $grid->setSource($source);
        
        // add an action column to edit the product type
        $rowAction = new RowAction('Edit', 'pim_catalog_producttype_edit');
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);
        
        return $grid->getGridResponse('PimCatalogBundle:ProductType:list.html.twig');
    }
    
    /**
     * Form to create a ProductType entity
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/add")
     * @Template()
     */
    public function addAction()
    {
        $type = new ProductTypeMongo();
        $setType = new ProductTypeType();
        
        $form = $this->createForm($setType, $type);
        
        return $this->render(
                'PimCatalogBundle:ProductType:add.html.twig', array('form' => $form->createView())
        );
    }
    
    /**
     * Insert a ProductType entity in database
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/create")
     * @Template()
     */
    public function createAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $type = new ProductTypeMongo();
            $setType = new ProductTypeType();
            
            $form = $this->createForm($setType, $type);
            
            $form->bind($request);
            if ($form->isValid()) {
                $this->init();
                
                $this->managerService->persist($type);
                $this->managerService->flush();
                
                $this->get('session')->setFlash('info', 'product type created');
            } else {
                $this->get('session')->setFlasg('error', 'Error during product type creation');
            }
        }
        
        
        return $this->render(
                'PimCatalogBundle:ProductType:create.html.twig', array('form' => $form->createView())
        );
    }
    
    /**
     * 
     * @param Request $request
     * 
     * @Route("/edit/{id}")
     * @Template()
     */
    public function editAction($id)
    {
        $productType = $this->getProductType($id);
        
        $setType = new ProductTypeType();
        
        $form = $this->createForm($setType, $productType);
        
        
        
        $formGroup = $this->addGroupAction($id)->getContent();
        
        
//         $renderVars = array('form' => $form->createView());
        
//         if ($request->isMethod('POST')) {
//             $form->bind($request);
            
//             if ($form->isValid()) {
//                 $postData = $request->get('akeneo_producttype');
//                 $this->idProductType = isset($postData['id']) ? $postData['id'] : false;
                
//                 if ($id) {
                    
//                     echo 'update';
                    
//                     // update a product
//                     $this->get('session')->setFlash('info', 'not yet implemented.. prouct type must be updated');
                    
//                     $formGroup = $this->addGroupAction()->getContent();
                    
//                     $renderVars['formGroup'] = $formGroup;
                    
//                 } elseif (isset($copy)) {
//                     // create a product type copying
//                     $this->get('session')->setFlash('info', 'not yet implemented... must be created by copy !');
//                 } else {
//                     var_dump($type);
//                     echo '<hr />';
//                     var_dump($postData);
                    
//                     // create a product type
//                     $this->managerService->persist($type);
//                     $this->managerService->flush();
                
//                     $this->get('session')->setFlash('info', 'Product type created');
//                 }
//             }
//         } else {
//             $this->get('session')->setFlash('info', 'no post for saving... must be redirect to edit or new..');
//         }
        
        return $this->render(
                'PimCatalogBundle:ProductType:edit.html.twig', array('form' => $form->createView(), 'formGroup' => $formGroup)
        );
    }
    
    /**
     * Update a ProductType entity in database
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/update")
     * @Template()
     */
    public function updateAction(Request $request)
    {
        return $this->render('PimCatalogBundle:ProductType:update.html.twig');
    }
    
    /**
     * Clone a ProductType entity
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/clone")
     * @Template()
     */
    public function cloneAction(Request $request)
    {
        return $this->render('PimCatalogBundle:ProductType:clone.html.twig');
    }
    
    /**
     * ajax request adding a group
     * 
     * @param Request $request
     * 
     * @Route("/addgroup/{id}")
     * @Template()
     */
    public function addGroupAction($id)
    {
        $groupType = new GroupType($id);
        $formGroup = $this->createForm($groupType);
        // set product type id in the form
        $formGroup->setData(array('idProductType' => $id));
        
        return $this->render(
                'PimCatalogBundle:ProductType:addGroup.html.twig', array('formGroup' => $formGroup->createView())
        );
    }
    
    /**
     * create a group for a product type
     * 
     * @param Request $request
     * 
     * @Route("/createGroup")
     * @Template()
     */
    public function createGroupAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $postData = $request->get('akeneo_producttype_group');
            $idProductType = $postData['idProductType'];
            
            $groupType = new GroupType($idProductType);
            $formGroup = $this->createForm($groupType);
            
            $formGroup->bind($request);
            
            
            if ($formGroup->isValid()) {
                // récupération du product type
                $productType = $this->get($idProductType);
                $productType->addGroup($postData['code']);
                
                $this->managerService->persist($productType);
                $this->managerService->flush();
            }
        }
        return $this->render(
                'PimCatalogBundle:ProductType:createGroup.html.twig', array('formGroup' => $formGroup->createView())
        );
    }
    
    /**
     * Get a product mongo document from an id
     * 
     * @param integer $id
     * @return ProductTypeMongo
     */
    protected function getProductType($id)
    {
        $this->init();
        return $this->managerService->getRepository('PimCatalogBundle:ProductTypeMongo')->find($id);
    }
}
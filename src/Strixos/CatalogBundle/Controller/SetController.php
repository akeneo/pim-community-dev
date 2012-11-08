<?php

namespace Strixos\CatalogBundle\Controller;

use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;

use Doctrine\Common\Collections\Expr\Expression;

use Doctrine\Common\Collections\ExpressionBuilder;

use Doctrine\Common\Collections\Criteria;

use Doctrine\ODM\MongoDB\DocumentManager;

use Pim\Bundle\CatalogBundle\Document\ProductType;
use Pim\Bundle\CatalogBundle\Document\ProductField;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


use Strixos\CatalogBundle\Form\Type\SetType;
use Strixos\CatalogBundle\Entity\Set;

use Doctrine\Common\Persistence\ObjectManager;
/**
 *
 * @author Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class SetController extends Controller
{
    /**
     * @var ProductManager
     */
    protected $productManager;
    
    /**
     * @var ObjectManager
     */
    protected $objectManager;
    
    /**
     * @Route("/attributeset/index")
     * @Template()
     * 
     * TODO : Must redirect to list and list must be like this
     */
    public function indexAction()
    {
        $this->initManagers();
        
        $sets = $this->objectManager->getRepository('PimCatalogBundle:ProductType')->findAll();
        return $this->render('StrixosCatalogBundle:Set:index.html.twig', array('sets' => $sets));
    }
    
    /**
     * initialize product service and object manager
     */
    protected function initManagers()
    {
        $this->productManager = $this->get('pim.catalog.product_manager');
        $this->objectManager = $this->productManager->getPersistenceManager();
    }

    /**
    * @Route("/attributeset/new")
    * @Template()
    */
    public function newAction(Request $request)
    {
        $this->initManagers();
        
        $typeClass = $this->productManager->getTypeClass();
        $set = new $typeClass();
        
        $setType = new SetType();
        
        // set list of existing sets to prepare copy list
        $setType->setCopySetOptions($this->_getCopySetOptions());
        
        // prepare form
        $form = $this->createForm($setType, $set);
        // render form
        return $this->render(
            'StrixosCatalogBundle:Set:edit.html.twig', array('form' => $form->createView(),)
        );
    }

    /**
    * @Route("/attributeset/clone")
    * @Template()
    */
    public function cloneAction(Request $request)
    {
        $postData = $request->get('strixos_catalog_attributeset');
        $copyId = isset($postData['copyfromset']) ? $postData['copyfromset'] : false;
        if ($copyId) {
            $setCode = isset($postData['code']) ? $postData['code'] : false;
            
            $this->initManagers();
            $productType = $this->objectManager->getRepository('PimCatalogBundle:ProductType')->find($copyId);
            
            if ($request->getMethod() == 'POST') {
                // persist
                $cloneType = $this->productManager->cloneType($productType);
                $cloneType->setCode($setCode);
                $this->objectManager->persist($cloneType);
                $this->objectManager->flush();
                // success message and redirect
                $this->get('session')->setFlash('notice', 'Attribute set has been saved!');
                return $this->redirect(
                    $this->generateUrl('strixos_catalog_set_edit', array('id' => $cloneType->getId()))
                );
            }
        }
        // TODO exception
    }

    /**
     * @Route("/attributeset/edit/{id}")
     * @Template()
     */
    public function editAction($id)
    {
        $this->initManagers();
        $set = $this->objectManager->getRepository('PimCatalogBundle:ProductType')->find($id);
        
        if (!$set) {
            throw $this->createNotFoundException('No set found for id '.$id);
        }
        
        // set list of available attribute to prepare drag n drop list
        $setType = new SetType();
        $setType->setAvailableAttributeOptions($this->_getAvailableAttributeOptions($set));
        // prepare form
        $form = $this->createForm($setType, $set);
        // render form
        return $this->render(
            'StrixosCatalogBundle:Set:edit.html.twig', array('form' => $form->createView(),)
        );
    }

    /**
     * @Route("/attributeset/save")
     * @Template()
    */
    public function saveAction(Request $request)
    {
        // load existing object or create a new one
        $postData = $request->get('strixos_catalog_attributeset');
        $id = isset($postData['id']) ? $postData['id'] : false;
        $em = $this->getDoctrine()->getEntityManager();
        if ($id) {
            $set = $em->getRepository('StrixosCatalogBundle:Set')->find($id);
        } else {
            $copyId = isset($postData['copyfromset']) ? $postData['copyfromset'] : false;
            $setCode = isset($postData['code']) ? $postData['code'] : false;
            $copySet = $em->getRepository('StrixosCatalogBundle:Set')->find($copyId);
            $set = $copySet->copy($setCode);
        }
        // create and bind with form
        $form = $this->createForm(new SetType(), $set);
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request); // TODO : method bindRequest is deprecated.. use bind

            // TODO problem with form validation
            //if ($form->isValid()) {
                // persist attribute set
                $em->persist($set);
                $em->flush();
                // success message and redirect
                $this->get('session')->setFlash('notice', 'Attribute set has been saved!');
                return $this->redirect(
                    $this->generateUrl('strixos_catalog_set_edit', array('$id' => $set->getId()))
                );
            //}
            // TODO Validation errors
        }
        // TODO Exception
    }
    

    /**
     * @return array
     */
    private function _getCopySetOptions()
    {
        $sets = $this->objectManager->getRepository('PimCatalogBundle:ProductType')->findAll();
        $setIdToName = array();
        foreach ($sets as $set) {
            $setIdToName[$set->getId()]= $set->getCode();
        }
        return $setIdToName;
    }

    /**
    * @return array
    */
    private function _getAvailableAttributeOptions($entityType)
    {
        // get attribute ids TODO get from collection ?
        $attributeIds = array();
        foreach ($entityType->getGroups() as $group) {
            foreach ($group->getFields() as $attribute) {
                $attributeIds[]= $attribute;
            }
        }
        
        echo count($attributeIds) .'<br />';
        
        // get all attributes
        $this->initManagers();
        $attributes = $this->objectManager->getRepository('PimCatalogBundle:ProductField')->findAll();
        
        echo count($attributes->getFields()) .'<br />';
        
        // keep only not used fields
        
        
        return array_diff($attributes->getFields(), $attributeIds);
    }
}

<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;

use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;

use Pim\Bundle\CatalogBundle\Form\Type\ProductSetType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use APY\DataGridBundle\Grid\Action\RowAction;
use Pim\Bundle\UIBundle\Grid\Helper as GridHelper;

use \Exception;
use Pim\Bundle\CatalogBundle\Form\DataTransformer\ProductSetToArrayTransformer;
/**
 * Product set controller.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
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
     * @return ProductTemplateManager
     */
    protected function getProductTemplateManager()
    {
        return $this->get('pim.catalog.product_template_manager');
    }

    /**
     * @return ObjectManager
     */
    protected function getPersistenceManager()
    {
        return $this->getProductManager()->getPersistenceManager();
    }

    /**
     * Create set form
     *
     * @param ProductSet $set
     *
     * @return Form
     */
    protected function createSetForm($set)
    {
        $setClass = $this->getProductTemplateManager()->getEntityClass();
        $groupClass = $this->getProductTemplateManager()->getGroupClass();
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
     *
     * @return multitype
     */
    public function indexAction()
    {
        // creates simple grid based on entity or document (ORM or ODM)
        $source = GridHelper::getGridSource($this->getPersistenceManager(), $this->getProductTemplateManager()->getEntityShortname());

        $grid = $this->get('grid');
        $grid->setSource($source);

        // add action columns
        $grid->setActionsColumnSeparator('&nbsp;');
        $rowAction = new RowAction('Edit', 'pim_catalog_productset_edit', false, '_self', array('class' => 'grid_action ui-icon-fugue-pencil'));
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);

        $rowAction = new RowAction('Delete', 'pim_catalog_productset_delete', true, '_self', array('class' => 'grid_action ui-icon-fugue-minus'));
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);

        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('PimCatalogBundle:ProductSet:index.html.twig');
    }

    /**
     * @param Request $request
     *
     * @Route("/new")
     * @Template()
     *
     * @return multitype
     */
    public function newAction(Request $request)
    {
        // create new product set
        $entity = $this->getProductTemplateManager()->getNewEntityInstance();

        // prepare form
        $form = $this->createSetForm($entity);

        return $this->render('PimCatalogBundle:ProductSet:new.html.twig', array('form' => $form->createView()));
    }

    /**
     * @param Request $request
     *
     * @Route("/create")
     * @Method("POST")
     * @Template()
     *
     * @return multitype
     */
    public function createAction(Request $request)
    {
        // clone product set
        $postData = $request->get('pim_catalogbundle_productattributeset');
        $copy = $postData['copyfromset'];
        if ($copy !== '') {
            $productType = $this->getProductTemplateManager()->getEntityRepository()->find($copy);
            $entity = $this->getProductTemplateManager()->cloneSet($productType);
            $entity->setCode($postData['code']);
            $entity->setTitle($postData['title']);
        } else {
            $entity = $this->getProductTemplateManager()->getNewEntityInstance();
            $entity->setCode($postData['code']);
            $entity->setTitle($postData['title']);
        }

        try {
            // persist
            $this->getPersistenceManager()->persist($entity);
            $this->getPersistenceManager()->flush();

            $this->get('session')->setFlash('success', 'product set has been created');

            return $this->redirect($this->generateUrl('pim_catalog_productset_edit', array('id' => $entity->getId())));

        } catch (\Exception $e) {
            $this->get('session')->setFlash('error', $e->getMessage());
        }

        $form = $this->createSetForm($entity);

        return $this->render('PimCatalogBundle:ProductSet:new.html.twig', array('form' => $form->createView()));
    }

    /**
     * @param integer $id
     *
     * @Route("/{id}/edit")
     * @Template()
     *
     * @return multitype
     */
    public function editAction($id)
    {
        $entity = $this->getProductTemplateManager()->getEntityRepository()->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No product set found for id '. $id);
        }

        // prepare & render form
        $form = $this->createSetForm($entity);

        return $this->render('PimCatalogBundle:ProductSet:edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
    }

    /**
     * update action
     *
     * @param Request $request the request
     * @param integer $id      set id
     *
     * @Route("/{id}/update")
     * @Method("POST")
     * @Template()
     *
     * @return multitype
     */
    public function updateAction(Request $request, $id)
    {
        // TODO avoid to load twice !
        $entity = $this->getProductTemplateManager()->getEntityRepository()->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No product set found for id '. $id);
        }

        // get product set
        $postData = $request->get('pim_catalogbundle_productattributeset');

        // transform to array understandable by array to set transformer
        $setData = array();
        $setData['id']     = $id;
        $setData['code']   = $postData['code'];
        $setData['title']  = $postData['title'];
        $setData['groups'] = $postData['groups'];
        // format group
        foreach ($setData['groups'] as $indexGrp => $group) {
            $setData['groups'][$group['code']]= $group;
            unset($setData['groups'][$indexGrp]);
            // format attributes
            if (isset($setData['groups'][$group['code']]['attributes'])) {
                foreach ($setData['groups'][$group['code']]['attributes'] as $indexAtt => $attribute) {
                    $setData['groups'][$group['code']]['attributes'][$indexAtt]= current($attribute);
                }
            }
        }

        // array to set
        $transformer = new ProductSetToArrayTransformer($this->getProductManager(), $this->getProductTemplateManager());
        $entity = $transformer->reverseTransform($setData);

        // persist
        try {
            $this->getPersistenceManager()->persist($entity);
            $this->getPersistenceManager()->flush();

            $this->get('session')->setFlash('success', 'product set has been updated');

            return $this->redirect(
                $this->generateUrl('pim_catalog_productset_edit', array('id' => $entity->getId()))
            );
        } catch (\Exception $e) {
            $this->get('session')->setFlash('error', $e->getMessage());
        }

        $form = $this->createSetForm($entity);

        return $this->render('PimCatalogBundle:ProductSet:edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));

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
     *
     * @return multitype
     */
    public function deleteAction($id)
    {
        $entity = $this->getProductTemplateManager()->getEntityRepository()->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No product set found for id '. $id);
        }

        $this->getPersistenceManager()->remove($entity);
        $this->getPersistenceManager()->flush();

        $this->get('session')->setFlash('success', 'product has been removed');

        return $this->redirect($this->generateUrl('pim_catalog_productset_index'));
    }

    /**
     * Get attributes
     *
     * @param ProductSet $set
     *
     * @return ArrayCollection
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
        $sets = $this->getProductTemplateManager()->getEntityRepository()->findAll();
        $setIdToName = array();
        foreach ($sets as $set) {
            $setIdToName[$set->getId()]= $set->getCode();
        }

        return $setIdToName;
    }
}

<?php

namespace Pim\Bundle\ProductBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Pim\Bundle\ProductBundle\Helper\CategoryHelper;
use Pim\Bundle\ProductBundle\Entity\Category;
use Pim\Bundle\ProductBundle\Entity\CategoryTranslation;

/**
 * Category Tree Controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/category-tree")
 */
class CategoryTreeController extends Controller
{
    /**
     * Index action
     *
     * @Route("/")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * List category trees. The select_node_id request parameter
     * allow to send back the tree where the node belongs with a selected
     * attribute
     *
     * @Route("/list-tree.{_format}", requirements={"_format"="json"})
     * @Template()
     *
     * @return array
     */
    public function listTreeAction()
    {
        $selectNodeId = $this->getRequest()->get('select_node_id');
        $selectNode = null;

        if ($selectNodeId != null) {
            try {
                $selectNode = $this->findCategory($selectNodeId);
            } catch (NotFoundHttpException $e) {
                $selectNode = null;
            }
        }

        $trees = $this->getTreeManager()->getTrees();

        $treesResponse = CategoryHelper::treesResponse($trees, $selectNode);

        return array('trees' => $treesResponse);
    }

    /**
     * Move a node
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/move-node")
     */
    public function moveNodeAction(Request $request)
    {
        $segmentId = $request->get('id');
        $parentId = $request->get('parent');
        $prevSiblingId = $request->get('prev_sibling');

        if ($request->get('copy') == 1) {
            $this->getTreeManager()->copy($segmentId, $parentId, $prevSiblingId);
        } else {
            $this->getTreeManager()->move($segmentId, $parentId, $prevSiblingId);
        }

        $this->getTreeManager()->getStorageManager()->flush();

        return new JsonResponse(array('status' => 1));
    }

    /**
     * List children of a category.
     * The parent category is provided via its id ('id' request parameter).
     * The node category to select is given by 'select_node_id' request parameter.
     *
     * If the node to select is not a direct child of the parent category, the tree
     * is expanded until the selected node is found amongs the children
     *
     * @Route("/children.{_format}", requirements={"_format"="json"})
     * @Template()
     *
     * @return array
     */
    public function childrenAction()
    {
        try {
            $parent = $this->findCategory($this->getRequest()->get('id'));
        } catch (NotFoundHttpException $e) {
            return array('data' => array());
        }

        $selectNodeId = $this->getRequest()->get('select_node_id');
        $withProductsCount = $this->getRequest()->get('with_products_count', false);
        $includeParent = $this->getRequest()->get('include_parent', false);

        $selectNode = null;

        if ($selectNodeId != null) {
            try {
                $selectNode = $this->findCategory($selectNodeId);
            } catch (NotFoundHttpException $e) {
                $selectNode = null;
            }
        }

        if (($selectNode != null)
            && (!$this->getTreeManager()->isAncestor($parent, $selectNode))) {
            $selectNode = null;
        }

        // FIXME: Simplify and use a single helper method able to manage both cases
        if ($selectNode != null) {
            $categories = $this->getTreeManager()->getChildren($parent->getId(), $selectNode->getId());
            if ($includeParent) {
                $data = CategoryHelper::childrenTreeResponse($categories, $selectNode, $withProductsCount, $parent);
            } else {
                $data = CategoryHelper::childrenTreeResponse($categories, $selectNode, $withProductsCount);
            }

        } else {
            $categories = $this->getTreeManager()->getChildren($parent->getId());
            if ($includeParent) {
                $data = CategoryHelper::childrenResponse($categories, $withProductsCount, $parent);
            } else {
                $data = CategoryHelper::childrenResponse($categories, $withProductsCount);
            }
        }

        return array('data' => $data);
    }

    /**
     * List products associated with the provided category
     *
     * @param Category $category
     *
     * @Route("/list-items.{_format}/{id}", requirements={"_format"="json", "id"="\d+"})
     * @Template()
     *
     * @return array
     *
     */
    public function listItemsAction(Category $category)
    {
        $products = new ArrayCollection();

        if (is_object($category)) {
            $products = $category->getProducts();
        }

        $data = CategoryHelper::productsResponse($products);

        return array('data' => $data);
    }

    /**
     * Show tree in management mode
     *
     * @param Category $treeRoot
     *
     * @Route(
     *     "/manage/{treeRoot}",
     *     requirements={"treeRoot"="\d+"},
     *     defaults={"treeRoot"=0}
     * )
     * @Template("PimProductBundle:CategoryTree:manage.html.twig")
     *
     * @return array
     */
    public function manageAction(Category $treeRoot)
    {
        $categories = $this->getTreeManager()->getTreeCategories($treeRoot);

        return array('categories' => $categories);
    }

    /**
     * Add a node
     *
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/add")
     */
    public function addAction(Request $request)
    {
        $code     = $request->get('title');
        $parentId = $request->get('id');
        $parent   = $this->getTreeManager()->getEntityRepository()->find($parentId);

        /** @var Category */
        $category = $this->getTreeManager()->getSegmentInstance();
        $category->setParent($parent);
        // TODO : deal with code already exists case
        $category->setCode($code);
        // TODO : could be remove after locale refactoring
        $categoryTranslation = $category->getTranslation('default');
        $categoryTranslation->setTitle($code);

        $sm = $this->getTreeManager()->getStorageManager();
        $sm->persist($category);
        $sm->flush();

        return new JsonResponse(array('status' => 1, 'id' => $category->getId()));
    }

    /**
     * Create category action
     *
     * @param Category $parent
     *
     * @Route(
     *     "/create/{parent}",
     *     requirements={"parent"="\d+"},
     *     defaults={"parent"=0}
     * )
     * @Template("PimProductBundle:CategoryTree:edit.html.twig")
     *
     * @return array
     */
    public function createAction(Category $parent = null)
    {
        if ($parent) {
            $category = $this->getTreeManager()->getSegmentInstance();
            $category->setParent($parent);
        } else {
            $category = $this->getTreeManager()->getTreeInstance();
        }

        $form    = $this->createForm($this->get('pim_product.form.type.category'), $category);
        $request = $this->getRequest();

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $sm = $this->getTreeManager()->getStorageManager();
                $sm->persist($category);
                $sm->flush();

                $this->addFlash(
                    'success',
                    sprintf('%s successfully created.', $category->getParent() ? 'Category' : 'Tree')
                );

                return $this->redirect(
                    $this->generateUrl(
                        'pim_product_categorytree_edit',
                        array('id'=> $category->getId(), 'node' => $category->getId())
                    )
                );
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Edit tree action
     *
     * @param Category $category The category to manage
     *
     * @Route("/myedit")
     * @Template("PimProductBundle:CategoryTree:form.html.twig")
     *
     * @return array
     */
    public function myeditAction()
    {
        $request  = $this->getRequest();
        $categoryId = $request->get('id');
        $category   = $this->getTreeManager()->getEntityRepository()->find($categoryId);
        if (!$category) {
            $category = $this->getTreeManager()->getSegmentInstance();
        }
        $form = $this->createForm($this->get('pim_product.form.type.category'), $category);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $sm = $this->getTreeManager()->getStorageManager();
                $sm->persist($category);
                $sm->flush();

                $this->addFlash(
                    'success',
                    sprintf('%s successfully updated.', $category->getParent() ? 'Category' : 'Tree')
                );

                return array('form' => $form->createView(),);
            }
        }

        return array('form' => $form->createView(),);
    }

    /**
     * Edit tree action
     *
     * @param Category $category The category to manage
     *
     * @Route(
     *     "/edit/{id}",
     *     requirements={"id"="\d+"},
     *     defaults={"id"=0}
     * )
     * @Template("PimProductBundle:CategoryTree:edit.html.twig")
     *
     * @return array
     */
    public function editAction(Category $category)
    {
        $request  = $this->getRequest();
        $datagrid = $this->getDataAuditDatagrid(
            $category,
            'pim_product_categorytree_edit',
            array(
                'id' => $category->getId()
            )
        );

        /*
        if ($request->isXmlHttpRequest()) {
            return $this->render('OroGridBundle:Datagrid:list.json.php', array('datagrid' => $datagrid->createView()));
        }*/

        $form = $this->createForm($this->get('pim_product.form.type.category'), $category);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $sm = $this->getTreeManager()->getStorageManager();
                $sm->persist($category);
                $sm->flush();

                $this->addFlash(
                    'success',
                    sprintf('%s successfully updated.', $category->getParent() ? 'Category' : 'Tree')
                );

                return $this->redirect(
                    $this->generateUrl(
                        'pim_product_categorytree_edit',
                        array('id'=> $category->getId(), 'node' => $category->getId())
                    )
                );
            }
        }

        return array(
            'form'     => $form->createView(),
            //'datagrid' => $datagrid->createView(),
        );
    }

    /**
     * Remove category tree
     *
     * @param Category $category The category to delete
     *
     * @Route(
     *     "/{id}/remove.{_format}",
     *     requirements={"_format"="json|html", "id"="\d+"},
     *     defaults={"_format"="html", "id"="\d+"}
     * )
     * @Method("DELETE")
     * @Template()
     *
     * @return array
     */
    public function removeAction(Category $category)
    {
        $count = $this->getTreeManager()->getEntityRepository()->countProductsLinked($category, false);
        $parent = $category->getParent();

        if ($count == 0) {
            $this->getTreeManager()->remove($category);
            $this->getTreeManager()->getStorageManager()->flush();
        } else {
            $errorMessage = 'They are products in this category, but they will not be deleted';
            if ($this->getRequest()->isXmlHttpRequest()) {
                return new JsonResponse($errorMessage, 400);
            } else {
                $this->addFlash('error', $errorMessage);

                return $this->redirect(
                    $this->generateUrl('pim_product_categorytree_index', array('node' => $category->getId()))
                );
            }
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new JsonResponse();
        } else {
            $this->addFlash('success', 'Category successfully removed');
            $params = ($parent !== null) ? array('node' => $parent->getId()) : array();

            return $this->redirect($this->generateUrl('pim_product_categorytree_index', $params));
        }
    }

    /**
     * Find a category from its id
     *
     * @param integer $categoryId
     *
     * @return Category
     */
    protected function findCategory($categoryId)
    {
        $category = $this->getTreeManager()->getEntityRepository()->find($categoryId);

        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        return $category;
    }

    /**
     * Get category tree manager
     *
     * @return \Pim\Bundle\ProductBundle\Manager\CategoryManager
     */
    protected function getTreeManager()
    {
        return $this->container->get('pim_product.manager.category');
    }

    /**
     * Get category path
     *
     * @param Category $category
     *
     * @return multitype:integer
     */
    protected function getCategoryPath(Category $category)
    {
        return $this->getTreeManager()->getEntityRepository()->getPath($category);
    }
}

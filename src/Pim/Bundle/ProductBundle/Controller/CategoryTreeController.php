<?php

namespace Pim\Bundle\ProductBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\ProductBundle\Helper\CategoryHelper;
use Pim\Bundle\ProductBundle\Entity\Category;

/**
 * Category Tree Controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTreeController extends Controller
{
    /**
     * List category trees. The select_node_id request parameter
     * allow to send back the tree where the node belongs with a selected
     * attribute
     *
     * @Template
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
     * @Template
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
     * @Template
     * @return array
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
     * Create a tree or category
     *
     * @param integer $parent
     *
     * @Template("PimProductBundle:CategoryTree:edit.html.twig")
     * @return array
     */
    public function createAction($parent = null)
    {
        if ($parent) {
            $parent = $this->findCategory($parent);
            $category = $this->getTreeManager()->getSegmentInstance();
            $category->setParent($parent);
        } else {
            $category = $this->getTreeManager()->getTreeInstance();
        }

        $request = $this->getRequest();
        $category->setCode($request->get('title'));

        $form = $this->createForm($this->get('pim_product.form.type.category'), $category);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $manager = $this->getTreeManager()->getStorageManager();
                $manager->persist($category);
                $manager->flush();

                $this->addFlash(
                    'success',
                    sprintf('%s successfully created.', $category->getParent() ? 'Category' : 'Tree')
                );

                $pendingManager = $this->container->get('pim_versioning.manager.pending');
                if ($pending = $pendingManager->getPending($category)) {
                    $pendingManager->createVersionAndAudit($pending);
                }

                return $this->redirectToRoute('pim_product_categorytree_edit', array('id' => $category->getId()));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Edit tree action
     *
     * @param Category $category
     *
     * @Template
     * @return array
     */
    public function editAction(Category $category)
    {
        $request = $this->getRequest();

        $datagrid = $this->getDataAuditDatagrid(
            $category,
            'pim_product_categorytree_edit',
            array(
                'id' => $category->getId()
            )
        );

        if ('json' == $request->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagrid->createView());
        }

        $form = $this->createForm($this->get('pim_product.form.type.category'), $category);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $manager = $this->getTreeManager()->getStorageManager();
                $manager->persist($category);
                $manager->flush();

                $this->addFlash(
                    'success',
                    sprintf('%s successfully updated.', $category->getParent() ? 'Category' : 'Tree')
                );

                $pendingManager = $this->container->get('pim_versioning.manager.pending');
                if ($pending = $pendingManager->getPending($category)) {
                    $pendingManager->createVersionAndAudit($pending);
                }
            }
        }

        return array(
            'form' => $form->createView(),
            'datagrid' => $datagrid->createView(),
        );
    }

    /**
     * Remove category tree
     *
     * @param Category $category
     *
     * @return RedirectResponse
     */
    public function removeAction(Category $category)
    {
        $productCount = $this->getTreeManager()->getEntityRepository()->countProductsLinked($category, false);
        $childrenCount = $this->getTreeManager()->getEntityRepository()->countChildren($category);

        if ((int) $childrenCount > 0) {
            $message = sprintf(
                'This category can not be deleted because it contains %s child categories',
                $childrenCount
            );
        } elseif ((int) $productCount > 0) {
            $message = sprintf('This category can not be deleted because it contains %s products', $productCount);
        } else {
            $this->getTreeManager()->remove($category);
            $this->getTreeManager()->getStorageManager()->flush();

            $this->addFlash('success', 'Category successfully removed');
            $parent = $category->getParent();
            $params = ($parent !== null) ? array('node' => $parent->getId()) : array();

            return $this->redirectToRoute('pim_product_categorytree_create', $params);
        }

        $this->addFlash('error', $message);

        return $this->redirectToRoute('pim_product_categorytree_edit', array('id' => $category->getId()));
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
}

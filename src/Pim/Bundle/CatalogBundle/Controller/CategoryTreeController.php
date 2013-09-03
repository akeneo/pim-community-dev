<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Pim\Bundle\CatalogBundle\Helper\CategoryHelper;
use Pim\Bundle\CatalogBundle\Entity\Category;

/**
 * Category Tree Controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Acl(
 *      id="pim_catalog_category",
 *      name="Category manipulation",
 *      description="Category manipulation",
 *      parent="pim_catalog"
 * )
 */
class CategoryTreeController extends Controller
{
    /**
     * List category trees. The select_node_id request parameter
     * allow to send back the tree where the node belongs with a selected
     * attribute
     * @param Request $request
     * @Template
     * @Acl(
     *      id="pim_catalog_category_list",
     *      name="View tree list",
     *      description="View tree list",
     *      parent="pim_catalog_category"
     * )
     * @return array
     */
    public function listTreeAction(Request $request)
    {
        $selectNodeId = $request->get('select_node_id');
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
     * @Acl(
     *      id="pim_catalog_category_move",
     *      name="Move category",
     *      description="Move category",
     *      parent="pim_catalog_category"
     * )
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
     * @param Request $request
     *
     * @Template
     * @Acl(
     *      id="pim_catalog_category_children",
     *      name="See category children",
     *      description="See category children",
     *      parent="pim_catalog_category"
     * )
     * @return array
     */
    public function childrenAction(Request $request)
    {
        try {
            $parent = $this->findCategory($request->get('id'));
        } catch (NotFoundHttpException $e) {
            return array('data' => array());
        }

        $selectNodeId      = $request->get('select_node_id');
        $withProductsCount = $request->get('with_products_count', false);
        $includeParent     = $request->get('include_parent', false);

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
     * @Acl(
     *      id="pim_catalog_category_products",
     *      name="See category's products",
     *      description="See category's products",
     *      parent="pim_catalog_category"
     * )
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
     * @param Request $request
     * @param integer $parent
     *
     * @Template("PimCatalogBundle:CategoryTree:edit.html.twig")
     * @Acl(
     *      id="pim_catalog_category_create",
     *      name="Create a category",
     *      description="Create a category",
     *      parent="pim_catalog_category"
     * )
     * @return array
     */
    public function createAction(Request $request, $parent = null)
    {
        if ($parent) {
            $parent = $this->findCategory($parent);
            $category = $this->getTreeManager()->getSegmentInstance();
            $category->setParent($parent);
        } else {
            $category = $this->getTreeManager()->getTreeInstance();
        }

        $category->setCode($request->get('title'));

        $form = $this->createForm($this->get('pim_catalog.form.type.category'), $category);

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
                if ($pending = $pendingManager->getPendingVersion($category)) {
                    $pendingManager->createVersionAndAudit($pending);
                }

                return $this->redirectToRoute('pim_catalog_categorytree_edit', array('id' => $category->getId()));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Edit tree action
     *
     * @param Request  $request
     * @param Category $category
     * @Template
     * @Acl(
     *      id="pim_catalog_category_edit",
     *      name="Edit a category",
     *      description="Edit a category",
     *      parent="pim_catalog_category"
     * )
     * @return array
     */
    public function editAction(Request $request, Category $category)
    {
        $datagrid = $this->getDataAuditDatagrid(
            $category,
            'pim_catalog_categorytree_edit',
            array(
                'id' => $category->getId()
            )
        );

        if ('json' == $request->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagrid->createView());
        }

        $form = $this->createForm($this->get('pim_catalog.form.type.category'), $category);

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
                if ($pending = $pendingManager->getPendingVersion($category)) {
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
     * @Acl(
     *      id="pim_catalog_category_remove",
     *      name="Remove a category",
     *      description="Remove a category",
     *      parent="pim_catalog_category"
     * )
     * @return RedirectResponse
     */
    public function removeAction(Category $category)
    {
        $parent = $category->getParent();
        $params = ($parent !== null) ? array('node' => $parent->getId()) : array();

        $this->getTreeManager()->remove($category);
        $this->getTreeManager()->getStorageManager()->flush();

        $this->addFlash('success', 'Category successfully removed');

        return $this->redirectToRoute('pim_catalog_categorytree_create', $params);
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
     * @return \Pim\Bundle\CatalogBundle\Manager\CategoryManager
     */
    protected function getTreeManager()
    {
        return $this->container->get('pim_catalog.manager.category');
    }
}

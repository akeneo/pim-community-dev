<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Oro\Bundle\GridBundle\Renderer\GridRenderer;
use Pim\Bundle\CatalogBundle\Datagrid\DatagridWorkerInterface;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Form\Type\CategoryType;
use Pim\Bundle\VersioningBundle\Manager\PendingManager;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\CatalogBundle\Helper\CategoryHelper;
use Pim\Bundle\CatalogBundle\Entity\Category;

/**
 * Category Tree Controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTreeController extends AbstractDoctrineController
{
    private $gridRenderer;
    private $dataGridWorker;
    private $categoryManager;
    private $categoryType;
    private $pendingManager;
            
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        RegistryInterface $doctrine,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        GridRenderer $gridRenderer,
        DatagridWorkerInterface $dataGridWorker,
        CategoryManager $categoryManager,
        CategoryType $categoryType,
        PendingManager $pendingManager
    ) {
        parent::__construct($request, $templating, $router, $securityContext, $doctrine, $formFactory, $validator);
        $this->gridRenderer = $gridRenderer;
        $this->dataGridWorker = $dataGridWorker;
        $this->categoryManager = $categoryManager;
        $this->categoryType = $categoryType;
        $this->pendingManager = $pendingManager;
    }
    
    /**
     * List category trees. The select_node_id request parameter
     * allow to send back the tree where the node belongs with a selected
     * attribute
     * @param Request $request
     *
     * @Template
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

        $trees = $this->categoryManager->getTrees();

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
            $this->categoryManager->copy($segmentId, $parentId, $prevSiblingId);
        } else {
            $this->categoryManager->move($segmentId, $parentId, $prevSiblingId);
        }

        $this->categoryManager->getStorageManager()->flush();

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
            && (!$this->categoryManager->isAncestor($parent, $selectNode))) {
            $selectNode = null;
        }

        // FIXME: Simplify and use a single helper method able to manage both cases
        if ($selectNode != null) {
            $categories = $this->categoryManager->getChildren($parent->getId(), $selectNode->getId());
            if ($includeParent) {
                $data = CategoryHelper::childrenTreeResponse($categories, $selectNode, $withProductsCount, $parent);
            } else {
                $data = CategoryHelper::childrenTreeResponse($categories, $selectNode, $withProductsCount);
            }

        } else {
            $categories = $this->categoryManager->getChildren($parent->getId());
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
     * @param Request $request
     * @param integer $parent
     *
     * @Template("PimCatalogBundle:CategoryTree:edit.html.twig")
     * @return array
     */
    public function createAction(Request $request, $parent = null)
    {
        if ($parent) {
            $parent = $this->findCategory($parent);
            $category = $this->categoryManager->getSegmentInstance();
            $category->setParent($parent);
        } else {
            $category = $this->categoryManager->getTreeInstance();
        }

        $category->setCode($request->get('title'));

        $form = $this->createForm($this->categoryType, $category);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $manager = $this->categoryManager->getStorageManager();
                $manager->persist($category);
                $manager->flush();

                $this->addFlash(
                    'success',
                    sprintf('%s successfully created.', $category->getParent() ? 'Category' : 'Tree')
                );

                if ($pending = $this->pendingManager->getPendingVersion($category)) {
                    $this->pendingManager->createVersionAndAudit($pending);
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
     *
     * @Template
     * @return array
     */
    public function editAction(Request $request, Category $category)
    {
        $datagrid = $this->dataGridWorker->getDataAuditDatagrid(
            $category,
            'pim_catalog_categorytree_edit',
            array(
                'id' => $category->getId()
            )
        );

        if ('json' == $request->getRequestFormat()) {
            return $this->gridRenderer->renderResultsJsonResponse($datagrid->createView());
        }

        $form = $this->createForm($this->categoryType, $category);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $manager = $this->categoryManager->getStorageManager();
                $manager->persist($category);
                $manager->flush();

                $this->addFlash(
                    'success',
                    sprintf('%s successfully updated.', $category->getParent() ? 'Category' : 'Tree')
                );

                if ($pending = $this->pendingManager->getPendingVersion($category)) {
                    $this->pendingManager->createVersionAndAudit($pending);
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
        $parent = $category->getParent();
        $params = ($parent !== null) ? array('node' => $parent->getId()) : array();

        $this->categoryManager->remove($category);
        $this->categoryManager->getStorageManager()->flush();

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
        $category = $this->categoryManager->getEntityRepository()->find($categoryId);

        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        return $category;
    }
}

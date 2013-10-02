<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Doctrine\Common\Collections\ArrayCollection;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\GridBundle\Renderer\GridRenderer;
use Oro\Bundle\UserBundle\Annotation\Acl;

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Datagrid\DatagridWorkerInterface;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Helper\CategoryHelper;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Exception\DeleteException;

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
class CategoryTreeController extends AbstractDoctrineController
{
    /**
     * @var GridRenderer
     */
    private $gridRenderer;

    /**
     * @var DatagridWorkerInterface
     */
    private $dataGridWorker;

    /**
     * @var CategoryManager
     */
    private $categoryManager;


    /**
     * Constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param RegistryInterface        $doctrine
     * @param GridRenderer             $gridRenderer
     * @param DatagridWorkerInterface  $dataGridWorker
     * @param CategoryManager          $categoryManager
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        RegistryInterface $doctrine,
        GridRenderer $gridRenderer,
        DatagridWorkerInterface $dataGridWorker,
        CategoryManager $categoryManager
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $doctrine
        );

        $this->gridRenderer    = $gridRenderer;
        $this->dataGridWorker  = $dataGridWorker;
        $this->categoryManager = $categoryManager;
    }

    /**
     * List category trees. The select_node_id request parameter
     * allow to send back the tree where the node belongs with a selected
     * attribute
     * @param Request $request
     *
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

        $trees = $this->categoryManager->getTrees();

        $treesResponse = CategoryHelper::treesResponse($trees, $selectNode);

        return array('trees' => $treesResponse);
    }

    /**
     * Move a node
     * @param Request $request
     *
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
        $withProductsCount = (boolean) $request->get('with_products_count', false);
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
            $category = $this->categoryManager->getSegmentInstance();
            $category->setParent($parent);
        } else {
            $category = $this->categoryManager->getTreeInstance();
        }

        $category->setCode($request->get('title'));

        $form = $this->createForm('pim_category', $category);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $manager = $this->categoryManager->getStorageManager();
                $manager->persist($category);
                $manager->flush();

                $this->addFlash('success', sprintf('flash.%s.created', $category->getParent() ? 'category' : 'tree'));

                return $this->redirectToRoute('pim_catalog_categorytree_edit', array('id' => $category->getId()));
            }
        }

        return $this->render(
            sprintf('PimCatalogBundle:CategoryTree:%s.html.twig', $request->get('content', 'edit')),
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Edit tree action
     *
     * @param Request  $request
     * @param Category $category
     *
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

        $form = $this->createForm('pim_category', $category);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $manager = $this->categoryManager->getStorageManager();
                $manager->persist($category);
                $manager->flush();

                $this->addFlash('success', sprintf('flash.%s.updated', $category->getParent() ? 'category' : 'tree'));
            }
        }

        return $this->render(
            sprintf('PimCatalogBundle:CategoryTree:%s.html.twig', $request->get('content', 'edit')),
            array(
                'form'     => $form->createView(),
                'datagrid' => $datagrid->createView(),
            )
        );
    }

    /**
     * Remove category tree
     *
     * @param Category $category
     *
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

        if (count($category->getChannels())) {
            throw new DeleteException($this->getTranslator()->trans('flash.tree.not removable'));
        }
        $this->categoryManager->remove($category);
        $this->categoryManager->getStorageManager()->flush();

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_catalog_categorytree_create', $params);
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
        $category = $this->categoryManager->getEntityRepository()->find($categoryId);

        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        return $category;
    }
}

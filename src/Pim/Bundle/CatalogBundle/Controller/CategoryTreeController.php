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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\GridBundle\Helper\DatagridHelperInterface;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Exception\DeleteException;

/**
 * Category Tree Controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTreeController extends AbstractDoctrineController
{
    /**
     * @var DatagridHelperInterface
     */
    private $datagridHelper;

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
     * @param DatagridHelperInterface  $datagridHelper
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
        DatagridHelperInterface $datagridHelper,
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

        $this->datagridHelper  = $datagridHelper;
        $this->categoryManager = $categoryManager;
    }

    /**
     * List category trees. The select_node_id request parameter
     * allow to send back the tree where the node belongs with a selected
     * attribute
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_catalog_category_list")
     * @return array
     */
    public function listTreeAction(Request $request)
    {
        $selectNodeId = $request->get('select_node_id', -1);
        try {
            $selectNode = $this->findCategory($selectNodeId);
        } catch (NotFoundHttpException $e) {
            $selectNode = $this->getDefaultTree();
        }

        return array(
            'trees'          => $this->categoryManager->getTrees(),
            'selectedTreeId' => $selectNode->isRoot() ? $selectNode->getId() : $selectNode->getRoot(),
            'include_sub'    => (bool) $this->getRequest()->get('include_sub', false),
        );
    }

    /**
     * Move a node
     * @param Request $request
     *
     * @AclAncestor("pim_catalog_category_move")
     * @return Response
     */
    public function moveNodeAction(Request $request)
    {
        $segmentId     = $request->get('id');
        $parentId      = $request->get('parent');
        $prevSiblingId = $request->get('prev_sibling');

        if ($request->get('copy') == 1) {
            $this->categoryManager->copy($segmentId, $parentId, $prevSiblingId);
        } else {
            $this->categoryManager->move($segmentId, $parentId, $prevSiblingId);
        }
        $this->categoryManager->getObjectManager()->flush();

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
     * @AclAncestor("pim_catalog_category_children")
     * @return array
     */
    public function childrenAction(Request $request)
    {
        try {
            $parent = $this->findCategory($request->get('id'));
        } catch (NotFoundHttpException $e) {
            return array('categories' => array());
        }

        $selectNodeId      = $this->getRequest()->get('select_node_id', -1);
        $withProductsCount = (bool) $this->getRequest()->get('with_products_count', false);
        $includeParent     = (bool) $this->getRequest()->get('include_parent', false);
        $includeSub        = (bool) $this->getRequest()->get('include_sub', false);

        try {
            $selectNode = $this->findCategory($selectNodeId);

            if (!$this->categoryManager->isAncestor($parent, $selectNode)) {
                $selectNode = null;
            }
        } catch (NotFoundHttpException $e) {
            $selectNode = null;
        }

        if ($selectNode !== null) {
            $categories = $this->categoryManager->getChildren($parent->getId(), $selectNode->getId());
            $view = 'PimCatalogBundle:CategoryTree:children-tree.json.twig';
        } else {
            $categories = $this->categoryManager->getChildren($parent->getId());
            $view = 'PimCatalogBundle:CategoryTree:children.json.twig';
        }

        return $this->render(
            $view,
            array(
                'categories'    => $categories,
                'parent'        => ($includeParent) ? $parent : null,
                'include_sub'   => $includeSub,
                'product_count' => $withProductsCount,
                'select_node'   => $selectNode
            ),
            new JsonResponse()
        );
    }

    /**
     * List products associated with the provided category
     *
     * @param Category $category
     *
     * @Template
     * @AclAncestor("pim_catalog_category_products")
     * @return array
     */
    public function listItemsAction(Category $category)
    {
        return array('products' => $category->getProducts());
    }

    /**
     * Create a tree or category
     *
     * @param Request $request
     * @param integer $parent
     *
     * @AclAncestor("pim_catalog_category_create")
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

        $category->setCode($request->get('label'));

        $form = $this->createForm('pim_category', $category, $this->getFormOptions($category));

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $manager = $this->categoryManager->getObjectManager();
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
     * @AclAncestor("pim_catalog_category_edit")
     * @return array
     */
    public function editAction(Request $request, Category $category)
    {
        $form = $this->createForm('pim_category', $category, $this->getFormOptions($category));

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $manager = $this->categoryManager->getObjectManager();
                $manager->persist($category);
                $manager->flush();

                $this->addFlash('success', sprintf('flash.%s.updated', $category->getParent() ? 'category' : 'tree'));
            }
        }

        return $this->render(
            sprintf('PimCatalogBundle:CategoryTree:%s.html.twig', $request->get('content', 'edit')),
            array(
                'form'            => $form->createView(),
                'historyDatagrid' => $this->getHistoryGrid($category)->createView()
            )
        );
    }

    /**
     * History of a category
     *
     * @param Request  $request
     * @param Category $category
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|template
     */
    public function historyAction(Request $request, Category $category)
    {
        $historyGridView = $this->getHistoryGrid($category)->createView();

        if ('json' === $request->getRequestFormat()) {
            return $this->datagridHelper->getDatagridRenderer()->renderResultsJsonResponse($historyGridView);
        }
    }

    /**
     * Remove category tree
     *
     * @param Category $category
     *
     * @AclAncestor("pim_catalog_category_remove")
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
        $this->categoryManager->getObjectManager()->flush();

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

    /**
     * Get default tree
     *
     * @throws \Exception
     *
     * @return Category
     */
    protected function getDefaultTree()
    {
        $defaultTree = $this->getUser()->getDefaultTree();

        if (!$defaultTree) {
            throw new \Exception('User must have a default tree defined');
        }

        return $defaultTree;
    }

    /**
     * Gets the options for the form
     *
     * @param Category $category
     *
     * @return array
     */
    protected function getFormOptions(Category $category)
    {
        return array();
    }

    /**
     * @param Category $category
     *
     * @return Datagrid
     */
    protected function getHistoryGrid(Category $category)
    {
        $historyGrid = $this->datagridHelper->getDataAuditDatagrid(
            $category,
            'pim_catalog_categorytree_history',
            array('id' => $category->getId())
        );

        return $historyGrid;
    }
}

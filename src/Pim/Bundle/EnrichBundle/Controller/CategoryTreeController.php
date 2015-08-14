<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\EnrichBundle\Event\CategoryEvents;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Classification\Factory\CategoryFactory;
use Pim\Component\Classification\Model\CategoryInterface;
use Pim\Component\Classification\Repository\CategoryRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Category Tree Controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTreeController extends Controller
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var CategoryManager */
    protected $categoryManager;

    /** @var UserContext */
    protected $userContext;

    /** @var SaverInterface */
    protected $categorySaver;

    /** @var RemoverInterface */
    protected $categoryRemover;

    /** @var CategoryFactory */
    protected $categoryFactory;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var string */
    protected $relatedEntity;

    /**
     * Constructor
     *
     * @param EventDispatcherInterface    $eventDispatcher
     * @param CategoryManager             $categoryManager
     * @param UserContext                 $userContext
     * @param SaverInterface              $categorySaver
     * @param RemoverInterface            $categoryRemover
     * @param CategoryFactory             $categoryFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param string                      $relatedEntity
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        CategoryManager $categoryManager,
        UserContext $userContext,
        SaverInterface $categorySaver,
        RemoverInterface $categoryRemover,
        CategoryFactory $categoryFactory,
        CategoryRepositoryInterface $categoryRepository,
        $relatedEntity
    ) {
        $this->eventDispatcher    = $eventDispatcher;
        $this->categoryManager    = $categoryManager;
        $this->userContext        = $userContext;
        $this->categorySaver      = $categorySaver;
        $this->categoryRemover    = $categoryRemover;
        $this->categoryFactory    = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->relatedEntity      = $relatedEntity;
    }

    /**
     * List category trees. The select_node_id request parameter
     * allow to send back the tree where the node belongs with a selected  reef attribute
     *
     * @param Request $request
     *
     * @return array
     *
     * @Template
     * @AclAncestor("pim_enrich_product_category_list")
     */
    public function listTreeAction(Request $request)
    {
        $selectNodeId  = $request->get('select_node_id', -1);

        try {
            $selectNode = $this->findCategory($selectNodeId);
        } catch (NotFoundHttpException $e) {
            $selectNode = $this->userContext->getUserCategoryTree($this->relatedEntity);
        }

        return [
            'trees'          => $this->categoryRepository->getTrees(),
            'selectedTreeId' => $selectNode->isRoot() ? $selectNode->getId() : $selectNode->getRoot(),
            'include_sub'    => (bool) $request->get('include_sub', false),
            'item_count'     => (bool) $request->get('with_items_count', true),
            'related_entity' => $this->relatedEntity
        ];
    }

    /**
     * Move a node
     *
     * @param Request $request
     *
     * @AclAncestor("pim_enrich_product_category_edit")
     *
     * @return Response
     */
    public function moveNodeAction(Request $request)
    {
        $categoryId    = $request->get('id');
        $parentId      = $request->get('parent');
        $prevSiblingId = $request->get('prev_sibling');

        // TODO: Change this in PIM-4409
        $this->categoryManager->move($categoryId, $parentId, $prevSiblingId);

        return new JsonResponse(['status' => 1]);
    }

    /**
     * List children of a category.
     * The parent category is provided via its id ('id' request parameter).
     * The node category to select is given by 'select_node_id' request parameter.
     *
     * If the node to select is not a direct child of the parent category, the tree
     * is expanded until the selected node is found amongs the children
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_enrich_product_category_list")
     *
     * @return array
     */
    public function childrenAction(Request $request)
    {
        try {
            $parent = $this->findCategory($request->get('id'));
        } catch (NotFoundHttpException $e) {
            return ['categories' => []];
        }

        $selectNodeId   = $request->get('select_node_id', -1);

        try {
            $selectNode = $this->findCategory($selectNodeId);

            if (!$this->categoryRepository->isAncestor($parent, $selectNode)) {
                $selectNode = null;
            }
        } catch (NotFoundHttpException $e) {
            $selectNode = null;
        }

        $categories = $this->getChildrenCategories($request, $selectNode);

        if (null === $selectNode) {
            $view = 'PimEnrichBundle:CategoryTree:children.json.twig';
        } else {
            $view = 'PimEnrichBundle:CategoryTree:children-tree.json.twig';
        }

        $withItemsCount = (bool) $request->get('with_items_count', false);
        $includeParent  = (bool) $request->get('include_parent', false);
        $includeSub     = (bool) $request->get('include_sub', false);

        return $this->render(
            $view,
            [
                'categories'     => $categories,
                'parent'         => ($includeParent) ? $parent : null,
                'include_sub'    => $includeSub,
                'item_count'     => $withItemsCount,
                'select_node'    => $selectNode,
                'related_entity' => $this->relatedEntity
            ],
            new JsonResponse()
        );
    }

    /**
     * @Template()
     * @AclAncestor("pim_enrich_product_category_list")
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'related_entity' => $this->relatedEntity,
        ];
    }

    /**
     * Create a tree or category
     *
     * @param Request $request
     * @param int     $parent
     *
     * @AclAncestor("pim_enrich_product_category_create")
     *
     * @return Response|RedirectResponse
     */
    public function createAction(Request $request, $parent = null)
    {
        $category = $this->categoryFactory->create();
        if ($parent) {
            $parent = $this->findCategory($parent);
            $category->setParent($parent);
        }

        $category->setCode($request->get('label'));
        $this->eventDispatcher->dispatch(CategoryEvents::PRE_CREATE, new GenericEvent($category));
        $form = $this->createForm('pim_category', $category, $this->getFormOptions($category));

        if ($request->isMethod('POST')) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->categorySaver->save($category);
                $this->addFlash('success', sprintf('flash.%s.created', $category->getParent() ? 'category' : 'tree'));
                $this->eventDispatcher->dispatch(CategoryEvents::POST_CREATE, new GenericEvent($category));

                return $this->redirectToRoute('pim_enrich_categorytree_edit', ['id' => $category->getId()]);
            }
        }

        return $this->render(
            sprintf('PimEnrichBundle:CategoryTree:%s.html.twig', $request->get('content', 'edit')),
            [
                'form'           => $form->createView(),
                'related_entity' => $this->relatedEntity,
            ]
        );
    }

    /**
     * Edit tree action
     *
     * @param Request $request
     * @param int     $id
     *
     * @AclAncestor("pim_enrich_product_category_edit")
     *
     * @return Response
     */
    public function editAction(Request $request, $id)
    {
        $category = $this->findCategory($id);
        $this->eventDispatcher->dispatch(CategoryEvents::PRE_EDIT, new GenericEvent($category));
        $form = $this->createForm('pim_category', $category, $this->getFormOptions($category));

        if ($request->isMethod('POST')) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->categorySaver->save($category);
                $this->addFlash('success', sprintf('flash.%s.updated', $category->getParent() ? 'category' : 'tree'));
                $this->eventDispatcher->dispatch(CategoryEvents::POST_EDIT, new GenericEvent($category));
            }
        }

        return $this->render(
            sprintf('PimEnrichBundle:CategoryTree:%s.html.twig', $request->get('content', 'edit')),
            [
                'form'           => $form->createView(),
                'related_entity' => $this->relatedEntity,
            ]
        );
    }

    /**
     * Remove category tree
     *
     * @param int $id
     *
     * @AclAncestor("pim_enrich_product_category_remove")
     *
     * @return Response|RedirectResponse
     */
    public function removeAction($id)
    {
        $category = $this->findCategory($id);
        $parent   = $category->getParent();
        $params   = ($parent !== null) ? ['node' => $parent->getId()] : [];

        $this->categoryRemover->remove($category, ['flush' => true]);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_enrich_categorytree_index', $params);
        }
    }

    /**
     * Find a category from its id
     *
     * @param int $categoryId
     *
     * @throws NotFoundHttpException
     *
     * @return CategoryInterface
     */
    protected function findCategory($categoryId)
    {
        $category = $this->categoryRepository->find($categoryId);

        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        return $category;
    }

    /**
     * Gets the options for the form
     *
     * @param CategoryInterface $category
     *
     * @return array
     */
    protected function getFormOptions(CategoryInterface $category)
    {
        return [];
    }

    /**
     * @param Request                $request
     * @param CategoryInterface|null $selectNode
     *
     * @return array|ArrayCollection
     */
    protected function getChildrenCategories(Request $request, $selectNode)
    {
        $parent = $this->findCategory($request->get('id'));

        if (null !== $selectNode) {
            $categories = $this->categoryRepository->getChildrenTreeByParentId($parent->getId(), $selectNode->getId());
        } else {
            $categories = $this->categoryRepository->getChildrenByParentId($parent->getId());
        }

        return $categories;
    }
}

<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\EnrichBundle\Event\CategoryEvents;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\UserBundle\Context\UserContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

    /** @var UserContext */
    protected $userContext;

    /** @var SaverInterface */
    protected $categorySaver;

    /** @var RemoverInterface */
    protected $categoryRemover;

    /** @var SimpleFactoryInterface */
    protected $categoryFactory;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var array */
    protected $rawConfiguration;

    /** @var SecurityFacade */
    protected $securityFacade;

    /**
     * Constructor
     *
     * @param EventDispatcherInterface    $eventDispatcher
     * @param UserContext                 $userContext
     * @param SaverInterface              $categorySaver
     * @param RemoverInterface            $categoryRemover
     * @param SimpleFactoryInterface      $categoryFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param SecurityFacade              $securityFacade
     * @param array                       $rawConfiguration
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        UserContext $userContext,
        SaverInterface $categorySaver,
        RemoverInterface $categoryRemover,
        SimpleFactoryInterface $categoryFactory,
        CategoryRepositoryInterface $categoryRepository,
        SecurityFacade $securityFacade,
        array $rawConfiguration
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->userContext = $userContext;
        $this->categorySaver = $categorySaver;
        $this->categoryRemover = $categoryRemover;
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->securityFacade = $securityFacade;

        $resolver = new OptionsResolver();
        $this->configure($resolver);

        $this->rawConfiguration = $resolver->resolve($rawConfiguration);
    }

    /**
     * List category trees. The select_node_id request parameter
     * allow to send back the tree where the node belongs with a selected  reef attribute
     *
     * @param Request $request
     *
     * @throws AccessDeniedException
     *
     * @return array
     *
     * @Template
     */
    public function listTreeAction(Request $request)
    {
        if (false === $this->securityFacade->isGranted($this->buildAclName('category_list'))) {
            throw new AccessDeniedException();
        }

        $selectNodeId = $request->get('select_node_id', -1);

        try {
            $selectNode = $this->findCategory($selectNodeId);
        } catch (NotFoundHttpException $e) {
            $selectNode = $this->userContext->getUserCategoryTree($this->rawConfiguration['related_entity']);
        }

        return [
            'trees'          => $this->categoryRepository->getTrees(),
            'selectedTreeId' => $selectNode->isRoot() ? $selectNode->getId() : $selectNode->getRoot(),
            'include_sub'    => (bool) $request->get('include_sub', false),
            'item_count'     => (bool) $request->get('with_items_count', true),
            'related_entity' => $this->rawConfiguration['related_entity']
        ];
    }

    /**
     * Move a node
     *
     * @param Request $request
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function moveNodeAction(Request $request)
    {
        if (false === $this->securityFacade->isGranted($this->buildAclName('category_edit'))) {
            throw new AccessDeniedException();
        }

        $category = $this->findCategory($request->get('id'));
        $parent = $this->findCategory($request->get('parent'));
        $category->setParent($parent);

        $prevSiblingId = $request->get('prev_sibling');
        $prevSibling = null;

        if (!empty($prevSiblingId)) {
            $prevSibling = $this->categoryRepository->find($prevSiblingId);
        }

        if (is_object($prevSibling)) {
            $this->categoryRepository->persistAsNextSiblingOf($category, $prevSibling);
        } else {
            $this->categoryRepository->persistAsFirstChildOf($category, $parent);
        }

        $this->categorySaver->save($category, ['flush' => true]);

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
     * @throws AccessDeniedException
     *
     * @Template
     *
     * @return Response
     */
    public function childrenAction(Request $request)
    {
        if (false === $this->securityFacade->isGranted($this->buildAclName('category_list'))) {
            throw new AccessDeniedException();
        }

        try {
            $parent = $this->findCategory($request->get('id'));
        } catch (\Exception $e) {
            $parent = $this->userContext->getUserProductCategoryTree();
        }

        $selectNodeId = $request->get('select_node_id', -1);

        try {
            $selectNode = $this->findCategory($selectNodeId);

            if (!$this->categoryRepository->isAncestor($parent, $selectNode)) {
                $selectNode = null;
            }
        } catch (NotFoundHttpException $e) {
            $selectNode = null;
        }

        $categories = $this->getChildrenCategories($request, $selectNode, $parent);

        if (null === $selectNode) {
            $view = 'PimEnrichBundle:CategoryTree:children.json.twig';
        } else {
            $view = 'PimEnrichBundle:CategoryTree:children-tree.json.twig';
        }

        $withItemsCount = (bool) $request->get('with_items_count', false);
        $includeParent = (bool) $request->get('include_parent', false);
        $includeSub = (bool) $request->get('include_sub', false);

        return $this->render(
            $view,
            [
                'categories'     => $categories,
                'parent'         => ($includeParent) ? $parent : null,
                'include_sub'    => $includeSub,
                'item_count'     => $withItemsCount,
                'select_node'    => $selectNode,
                'related_entity' => $this->rawConfiguration['related_entity']
            ],
            new JsonResponse()
        );
    }

    /**
     * @Template()
     *
     * @throws AccessDeniedException
     *
     * @return array
     */
    public function indexAction()
    {
        if (false === $this->securityFacade->isGranted($this->buildAclName('category_list'))) {
            throw new AccessDeniedException();
        }

        return [
            'related_entity' => $this->rawConfiguration['related_entity'],
            'route'          => $this->rawConfiguration['route'],
            'acl'            => $this->rawConfiguration['acl'],
        ];
    }

    /**
     * Create a tree or category
     *
     * @param Request $request
     * @param int     $parent
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function createAction(Request $request, $parent = null)
    {
        if (false === $this->securityFacade->isGranted($this->buildAclName('category_create'))) {
            throw new AccessDeniedException();
        }

        $category = $this->categoryFactory->create();
        if ($parent) {
            $parent = $this->findCategory($parent);
            $category->setParent($parent);
        }

        $category->setCode($request->get('label'));
        $this->eventDispatcher->dispatch(CategoryEvents::PRE_CREATE, new GenericEvent($category));
        $form = $this->createForm($this->rawConfiguration['form_type'], $category, $this->getFormOptions($category));

        if ($request->isMethod('POST')) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->categorySaver->save($category);
                $message = new Message(sprintf('flash.%s.created', $category->getParent() ? 'category' : 'tree'));
                $this->addFlash('success', $message);
                $this->eventDispatcher->dispatch(CategoryEvents::POST_CREATE, new GenericEvent($category));

                return new JsonResponse(
                    [
                        'route'  => 'categorytree_edit',
                        'params' => ['id' => $category->getId()]
                    ]
                );
            }
        }

        return $this->render(
            sprintf('PimEnrichBundle:CategoryTree:%s.html.twig', $request->get('content', 'edit')),
            [
                'form'           => $form->createView(),
                'related_entity' => $this->rawConfiguration['related_entity'],
                'acl'            => $this->rawConfiguration['acl'],
                'route'          => $this->rawConfiguration['route'],
            ]
        );
    }

    /**
     * Edit tree action
     *
     * @param Request $request
     * @param int     $id
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function editAction(Request $request, $id)
    {
        if (false === $this->securityFacade->isGranted($this->buildAclName('category_edit'))) {
            throw new AccessDeniedException();
        }

        $category = $this->findCategory($id);
        $this->eventDispatcher->dispatch(CategoryEvents::PRE_EDIT, new GenericEvent($category));
        $form = $this->createForm($this->rawConfiguration['form_type'], $category, $this->getFormOptions($category));

        if ($request->isMethod('POST')) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->categorySaver->save($category);
                $message = new Message(sprintf('flash.%s.updated', $category->getParent() ? 'category' : 'tree'));
                $this->addFlash('success', $message);
                $this->eventDispatcher->dispatch(CategoryEvents::POST_EDIT, new GenericEvent($category));
            }
        }

        return $this->render(
            sprintf('PimEnrichBundle:CategoryTree:%s.html.twig', $request->get('content', 'edit')),
            [
                'form'           => $form->createView(),
                'related_entity' => $this->rawConfiguration['related_entity'],
                'acl'            => $this->rawConfiguration['acl'],
                'route'          => $this->rawConfiguration['route'],
            ]
        );
    }

    /**
     * Remove category tree
     *
     * @param int $id
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function removeAction($id)
    {
        if (false === $this->securityFacade->isGranted($this->buildAclName('category_remove'))) {
            throw new AccessDeniedException();
        }

        $category = $this->findCategory($id);
        $parent = $category->getParent();
        $params = (null !== $parent) ? ['node' => $parent->getId()] : [];

        $this->categoryRemover->remove($category);

        return new Response('', 204);
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
    protected function getChildrenCategories(Request $request, $selectNode, $parent)
    {
        if (null !== $selectNode) {
            $categories = $this->categoryRepository->getChildrenTreeByParentId($parent->getId(), $selectNode->getId());
        } else {
            $categories = $this->categoryRepository->getChildrenByParentId($parent->getId());
        }

        return $categories;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function buildAclName($name)
    {
        return $this->rawConfiguration['acl'] . '_' . $name;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function buildRouteName($name)
    {
        return $this->rawConfiguration['route'] . '_' . $name;
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configure(OptionsResolver $resolver)
    {
        $resolver->setRequired(['related_entity', 'form_type', 'acl', 'route']);
    }
}

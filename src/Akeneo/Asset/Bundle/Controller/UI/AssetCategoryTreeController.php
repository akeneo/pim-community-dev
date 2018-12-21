<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Controller\UI;

use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Platform\Bundle\UIBundle\Flash\Message;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;
use function var_dump;

/**
 * @author Anael Chardan <anael.chardan@akeneo.com>
 */
class AssetCategoryTreeController
{
    /** @staticvar string */
    const CONTEXT_MANAGE = 'manage';

    /** @staticvar string */
    const CONTEXT_VIEW = 'view';

    /** @staticvar string */
    const CONTEXT_ASSOCIATE = 'associate';

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

    /** @var ObjectRepository */
    protected $categoryAccessRepo;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var EngineInterface */
    private $templating;

    /** @var EngineInterface */
    private $engine;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var SessionInterface */
    private $session;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param UserContext $userContext
     * @param SaverInterface $categorySaver
     * @param RemoverInterface $categoryRemover
     * @param SimpleFactoryInterface $categoryFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param SecurityFacade $securityFacade
     * @param array $rawConfiguration
     * @param CategoryAccessRepository $categoryAccessRepo
     * @param TokenStorageInterface $tokenStorage
     * @param string|null $indexTemplate
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        UserContext $userContext,
        SaverInterface $categorySaver,
        RemoverInterface $categoryRemover,
        SimpleFactoryInterface $categoryFactory,
        CategoryRepositoryInterface $categoryRepository,
        SecurityFacade $securityFacade,
        array $rawConfiguration,
        CategoryAccessRepository $categoryAccessRepo,
        TokenStorageInterface $tokenStorage,
        EngineInterface $engine,
        FormFactoryInterface $formFactory,
        SessionInterface $session
    ) {

        $this->eventDispatcher = $eventDispatcher;
        $this->userContext = $userContext;
        $this->categorySaver = $categorySaver;
        $this->categoryRemover = $categoryRemover;
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->securityFacade = $securityFacade;
        $this->categoryAccessRepo = $categoryAccessRepo;
        $this->tokenStorage = $tokenStorage;
        $this->engine = $engine;
        $this->formFactory = $formFactory;
        $this->session = $session;

        $resolver = new OptionsResolver();
        $resolver->setRequired(['related_entity', 'form_type', 'acl', 'route']);

        $this->rawConfiguration = $resolver->resolve($rawConfiguration);
    }

    /**
     * List Asset category trees. The select_node_id request parameter
     * allow to send back the tree where the node belongs with a selected  reef attribute
     *
     * @param Request $request
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function listTreeAction(Request $request): Response
    {
        if (false === $this->securityFacade->isGranted($this->buildAclName('category_list'))) {
            throw new AccessDeniedException();
        }

        $selectNodeId = $request->get('select_node_id', -1);
        $context = $request->get('context', false);

        if (self::CONTEXT_MANAGE === $context) {
            try {
                $selectNode = $this->findCategory($selectNodeId);
            } catch (NotFoundHttpException $e) {
                $selectNode = $this->userContext->getDefaultTree();
            }
            $grantedTrees = $this->categoryRepository->getTrees();
        } else {
            try {
                $selectNode = $this->findGrantedCategory($selectNodeId, $context);
            } catch (NotFoundHttpException $e) {
                $selectNode = $this->userContext->getAccessibleUserTree();
            } catch (AccessDeniedException $e) {
                $selectNode = $this->userContext->getAccessibleUserTree();
            }

            $grantedCategoryIds = $this->categoryAccessRepo->getGrantedCategoryIds(
                $this->tokenStorage->getToken()->getUser(),
                Attributes::VIEW_ITEMS
            );

            $grantedTrees = $this->categoryRepository->getGrantedTrees($grantedCategoryIds);
        }

        return new Response($this->engine->render(
            'AkeneoAssetBundle:CategoryTree:listTree.json.twig',
            [
            'trees'          => $grantedTrees,
            'selectedTreeId' => $selectNode->isRoot() ? $selectNode->getId() : $selectNode->getRoot(),
            'include_sub'    => (bool) $request->get('include_sub', false),
            'item_count'     => (bool) $request->get('with_items_count', true),
            'related_entity' => $this->rawConfiguration['related_entity'],
        ]));
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
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

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
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function indexAction(): Response
    {
        if (false === $this->securityFacade->isGranted($this->buildAclName('category_list'))) {
            throw new AccessDeniedException();
        }

        return new Response($this->engine->render(
            'AkeneoAssetBundle:CategoryTree:index.html.twig',
            [
                'related_entity' => $this->rawConfiguration['related_entity'],
                'route' => $this->rawConfiguration['route'],
                'acl' => $this->rawConfiguration['acl'],
            ]
        ));
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
        $form = $this->formFactory->create($this->rawConfiguration['form_type'], $category, []);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->categorySaver->save($category);
                $message = new Message(sprintf('flash.%s.created', $category->getParent() ? 'category' : 'tree'));
                $this->session->getFlashBag()->add('success', $message);

                return new JsonResponse(
                    [
                        'route' => $this->rawConfiguration['route'] . '_' . 'categorytree_edit',
                        'params' => ['id' => $category->getId()]
                    ]
                );
            }
        }

        return new Response($this->engine->render(
            sprintf('AkeneoAssetBundle:CategoryTree:%s.html.twig', $request->get('content', 'edit')),
            [
                'form'           => $form->createView(),
                'related_entity' => $this->rawConfiguration['related_entity'],
                'acl'            => $this->rawConfiguration['acl'],
                'route'          => $this->rawConfiguration['route'],
            ]
        ));
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
        $form = $this->formFactory->create($this->rawConfiguration['form_type'], $category, []);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->categorySaver->save($category);
                $message = new Message(sprintf('flash.%s.updated', $category->getParent() ? 'category' : 'tree'));
                $this->session->getFlashBag()->add('success', $message);
            }
        }

        return new Response($this->engine->render(
            sprintf('AkeneoAssetBundle:CategoryTree:%s.html.twig', $request->get('content', 'edit')),
            [
                'form'           => $form->createView(),
                'related_entity' => $this->rawConfiguration['related_entity'],
                'acl'            => $this->rawConfiguration['acl'],
                'route'          => $this->rawConfiguration['route'],
            ]
        ));
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
            $parent = $this->userContext->getAccessibleUserTree();
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
            $view = 'AkeneoAssetBundle:CategoryTree:children.json.twig';
        } else {
            $view = 'AkeneoAssetBundle:CategoryTree:children-tree.json.twig';
        }

        $withItemsCount = (bool) $request->get('with_items_count', false);
        $includeParent = (bool) $request->get('include_parent', false);
        $includeSub = (bool) $request->get('include_sub', false);

        return new Response($this->engine->render(
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
        ));
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
    public function removeAction(Request $request, $id)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (false === $this->securityFacade->isGranted($this->buildAclName('category_remove'))) {
            throw new AccessDeniedException();
        }

        $category = $this->findCategory($id);

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
            throw new NotFoundHttpException(sprintf('Category with id %s not found', $categoryId));
        }

        return $category;
    }


    /**
     * {@inheritdoc}
     */
    protected function getChildrenCategories(Request $request, $selectNode, $parent)
    {
        try {
            $parent = $this->findCategory($request->get('id'));
        } catch (NotFoundHttpException $e) {
            $parent = $this->userContext->getUserProductCategoryTree();
        }

        $isEditGranted = $this->securityFacade->isGranted($this->buildAclName('category_edit'));
        $context = $request->get('context', false);

        if ($isEditGranted && self::CONTEXT_MANAGE === $context) {
            if (null !== $selectNode) {
                $categories = $this->categoryRepository->getChildrenTreeByParentId($parent->getId(), $selectNode->getId());
            } else {
                $categories = $this->categoryRepository->getChildrenByParentId($parent->getId());
            }
        } else {
            $grantedCategoryIds = $this->categoryAccessRepo->getGrantedCategoryIds(
                $this->tokenStorage->getToken()->getUser()
                , Attributes::VIEW_ITEMS
            );

            if (null !== $selectNode) {
                $categories = $this->categoryRepository
                    ->getChildrenTreeByParentId($parent->getId(), $selectNode->getId(), $grantedCategoryIds);
            } else {
                $categories = $this->categoryRepository->getChildrenGrantedByParentId($parent, $grantedCategoryIds);
            }
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
     * Get granted categories
     *
     * @return int[]
     */
    protected function getGrantedCategories()
    {
        return $this->categoryAccessRepo->getGrantedCategoryIds($this->tokenStorage->getToken()->getUser(), Attributes::VIEW_ITEMS);
    }

    /**
     * Find a category from its id, trows an exception if not found or not granted
     *
     * @param int    $categoryId the category id
     * @param string $context    the retrieving context
     *
     * @throws AccessDeniedException
     *
     * @return CategoryInterface
     */
    protected function findGrantedCategory($categoryId, $context)
    {
        $allowed = [self::CONTEXT_MANAGE, self::CONTEXT_VIEW, self::CONTEXT_ASSOCIATE];
        if (!in_array($context, $allowed)) {
            throw new AccessDeniedException('You can not access this category');
        }

        $category = $this->findCategory($categoryId);

        if (self::CONTEXT_MANAGE === $context) {
            if (!$this->securityFacade->isGranted($this->buildAclName('category_edit'))) {
                throw new AccessDeniedException('You can not access this category');
            }

            return $category;
        }

        if (false === $this->securityFacade->isGranted(Attributes::VIEW_ITEMS, $category)) {
            throw new AccessDeniedException('You can not access this category');
        }

        return $category;
    }
}

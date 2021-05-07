<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\Ui;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Counter\CategoryItemsCounterInterface;
use Akeneo\Pim\Enrichment\Component\Category\Query\CountTreesChildrenInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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

    /** @var TranslatorInterface */
    protected $translator;

    private ObjectUpdaterInterface $categoryUpdater;

    private NormalizerInterface $normalizer;

    private ValidatorInterface $validator;

    private NormalizerInterface $constraintViolationNormalizer;

    private CategoryItemsCounterInterface $categoryItemsCounter;

    private CountTreesChildrenInterface $countTreesChildrenQuery;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        UserContext $userContext,
        SaverInterface $categorySaver,
        RemoverInterface $categoryRemover,
        SimpleFactoryInterface $categoryFactory,
        CategoryRepositoryInterface $categoryRepository,
        SecurityFacade $securityFacade,
        TranslatorInterface $translator,
        NormalizerInterface $normalizer,
        ObjectUpdaterInterface $categoryUpdater,
        ValidatorInterface $validator,
        NormalizerInterface $constraintViolationNormalizer,
        CategoryItemsCounterInterface $categoryItemsCounter,
        CountTreesChildrenInterface $countTreesChildrenQuery,
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
        $this->translator = $translator;
        $this->normalizer = $normalizer;
        $this->categoryUpdater = $categoryUpdater;
        $this->validator = $validator;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
        $this->categoryItemsCounter = $categoryItemsCounter;
        $this->countTreesChildrenQuery = $countTreesChildrenQuery;
    }

    /**
     * List category trees. The select_node_id request parameter
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

        try {
            $selectNode = $this->findCategory($selectNodeId);
        } catch (NotFoundHttpException $e) {
            $selectNode = $this->userContext->getUserCategoryTree($this->rawConfiguration['related_entity']);
        }

        return $this->render(
            'AkeneoPimEnrichmentBundle:CategoryTree:listTree.json.twig',
            [
                'trees'          => $this->categoryRepository->getTrees(),
                'selectedTreeId' => $selectNode->isRoot() ? $selectNode->getId() : $selectNode->getRoot(),
                'include_sub'    => (bool) $request->get('include_sub', false),
                'item_count'     => (bool) $request->get('with_items_count', true),
                'related_entity' => $this->rawConfiguration['related_entity']
            ]
        );
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
            $view = 'AkeneoPimEnrichmentBundle:CategoryTree:children.json.twig';
        } else {
            $view = 'AkeneoPimEnrichmentBundle:CategoryTree:children-tree.json.twig';
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
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function indexAction(): Response
    {
        if (false === $this->securityFacade->isGranted($this->buildAclName('category_list'))) {
            throw new AccessDeniedException();
        }

        return $this->render(
            'AkeneoPimEnrichmentBundle:CategoryTree:index.html.twig',
            [
                'related_entity' => $this->rawConfiguration['related_entity'],
                'route'          => $this->rawConfiguration['route'],
                'acl'            => $this->rawConfiguration['acl'],
            ]
        );
    }

    /**
     * Create a tree or category
     *
     * @param Request $request
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        if (false === $this->securityFacade->isGranted($this->buildAclName('category_create'))) {
            throw new AccessDeniedException();
        }

        $category = $this->categoryFactory->create();
        $data = json_decode($request->getContent(), true);
        $this->categoryUpdater->update($category, $data);
        $violations = $this->validator->validate($category);

        $normalizedViolations = [];
        foreach ($violations as $violation) {
            $normalizedViolation = $this->constraintViolationNormalizer->normalize(
                $violation,
                'internal_api',
                ['category' => $category]
            );
            $normalizedViolations[$normalizedViolation['path']] = $normalizedViolation['message'];
        }

        if (count($normalizedViolations) > 0) {
            return new JsonResponse($normalizedViolations, Response::HTTP_BAD_REQUEST);
        }

        $this->categorySaver->save($category);

        return new JsonResponse(null, JsonResponse::HTTP_CREATED);
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
        $form = $this->createForm($this->rawConfiguration['form_type'], $category, $this->getFormOptions($category));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->categorySaver->save($category);
                $message = sprintf('flash.%s.updated', $category->getParent() ? 'category' : 'tree');
                $this->addFlash('success', $this->translator->trans($message));
            }
        }

        $rootCategory = null;
        if ($category->isRoot() === false) {
            $rootCategory = $this->findCategory($category->getRoot());
        }

        $normalizedCategory = $this->normalizer->normalize($category, 'internal_api');
        $normalizedCategory = array_merge($normalizedCategory, [
            'root' => $rootCategory === null ? null : $this->normalizer->normalize($rootCategory, 'internal_api')
        ]);

        // @todo[PLG-94] remove unused Twig templates AkeneoPimEnrichmentBundle:CategoryTree:%s.html.twig
        return new JsonResponse($normalizedCategory,Response::HTTP_OK);
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


    public function getCategoryTreesProductsNumberAction(): JsonResponse
    {
        $trees = $this->categoryRepository->getTrees();

        $productsCountByCategories = array_fill_keys(
            array_map(fn (CategoryInterface $category) => $category->getId(), $trees),
            0
        );

        foreach ($trees as $tree) {
            $productsCountByCategories[$tree->getId()] = $this->categoryItemsCounter->getItemsCountInCategory($tree, true);
        }

        return new JsonResponse($productsCountByCategories);
    }

    public function countCategoryProducts(int $id): Response
    {
        $category = $this->findCategory($id);

        $numberOfProducts = $this->categoryItemsCounter->getItemsCountInCategory($category, true);

        return new JsonResponse($numberOfProducts);
    }

    public function countChildrenAction(): JsonResponse
    {
        $countChildren = $this->countTreesChildrenQuery->execute();

        return new JsonResponse($countChildren);
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

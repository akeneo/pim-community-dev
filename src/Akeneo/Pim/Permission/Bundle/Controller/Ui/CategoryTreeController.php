<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Controller\Ui;

use Akeneo\Category\Domain\Model\Classification\CategoryTree;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\Query\GetCategoryTreesInterface;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Category\Infrastructure\Symfony\Form\CategoryFormViewNormalizerInterface;
use Akeneo\Pim\Enrichment\Bundle\Controller\Ui\CategoryTreeController as BaseCategoryTreeController;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Counter\CategoryItemsCounterInterface;
use Akeneo\Pim\Enrichment\Component\Category\Query\CountTreesChildrenInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Exception\PublishedProductConsistencyException;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Overridden category controller
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class CategoryTreeController extends BaseCategoryTreeController
{
    /** @staticvar string */
    const CONTEXT_MANAGE = 'manage';

    /** @staticvar string */
    const CONTEXT_VIEW = 'view';

    /** @staticvar string */
    const CONTEXT_ASSOCIATE = 'associate';

    protected CategoryAccessRepository $categoryAccessRepo;
    protected TokenStorageInterface $tokenStorage;

    public function __construct(
        private UserContext $userContext,
        private SaverInterface $categorySaver,
        private RemoverInterface $categoryRemover,
        private SimpleFactoryInterface $categoryFactory,
        private CategoryRepositoryInterface $categoryRepository,
        private SecurityFacade $securityFacade,
        private NormalizerInterface $normalizer,
        private ObjectUpdaterInterface $categoryUpdater,
        private ValidatorInterface $validator,
        private NormalizerInterface $constraintViolationNormalizer,
        private CategoryItemsCounterInterface $categoryItemsCounter,
        private CountTreesChildrenInterface $countTreesChildrenQuery,
        CategoryFormViewNormalizerInterface $categoryFormViewNormalizer,
        private GetCategoryInterface $getCategory,
        private GetCategoryTreesInterface $getCategoryTrees,
        private FeatureFlags $featureFlags,
        array $rawConfiguration,
        CategoryAccessRepository $categoryAccessRepo,
        TokenStorageInterface $tokenStorage,
    ) {
        parent::__construct(
            $userContext,
            $categorySaver,
            $categoryRemover,
            $categoryFactory,
            $categoryRepository,
            $securityFacade,
            $normalizer,
            $categoryUpdater,
            $validator,
            $constraintViolationNormalizer,
            $categoryItemsCounter,
            $countTreesChildrenQuery,
            $categoryFormViewNormalizer,
            $getCategory,
            $getCategoryTrees,
            $featureFlags,
            $rawConfiguration,
        );

        $this->categoryAccessRepo = $categoryAccessRepo;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function listTreeAction(Request $request): Response
    {
        if (false === $this->securityFacade->isGranted($this->buildAclName('category_list'))) {
            throw new AccessDeniedException();
        }

        $selectNodeId = $request->get('select_node_id', -1);
        $context = $request->get('context', false);

        if (self::CONTEXT_MANAGE === $context) {
            $selectNode = $this->getCategory->byId($selectNodeId);
            if (!$selectNode) {
                $selectNode = $this->userContext->getDefaultTree();
            }

            $grantedTrees = $this->getCategoryTrees->getAll();
        } else {
            try {
                $selectNode = $this->findGrantedCategory($selectNodeId, $context);
            } catch (NotFoundHttpException $e) {
                $selectNode = $this->userContext->getAccessibleUserTree();
            } catch (AccessDeniedException $e) {
                $selectNode = $this->userContext->getAccessibleUserTree();
            }

            $grantedCategoryIds = $this->getGrantedCategories();
            $grantedTrees = $this->getCategoryTrees->byIds($grantedCategoryIds);
        }

        if ($selectNode instanceof CategoryTree) {
            $selectedTreeId = $selectNode->getId()->getValue();
        } else {
            $selectedTreeId = $selectNode->isRoot() ? $selectNode->getId() : $selectNode->getRoot();
        }

        $formatedTrees = array_map(function (CategoryTree $tree) use ($selectedTreeId) {
            return [
                'id' => $tree->getId()->getValue(),
                'code' => (string) $tree->getCode(),
                'label' => $tree->getLabel($this->userContext->getCurrentLocaleCode()),
                'templateUuid' => (string) $tree->getCategoryTreeTemplate()?->getTemplateUuid(),
                'templateLabel' => $tree->getCategoryTreeTemplate()?->getTemplateLabel($this->userContext->getCurrentLocaleCode()),
                'templateCode' => (string) $tree->getCategoryTreeTemplate()?->getTemplateCode(),
                'selected' => $tree->getId()?->getValue() === $selectedTreeId ? 'true' : 'false'
            ];
        }, $grantedTrees);

        return new JsonResponse($formatedTrees);
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
            $categories = parent::getChildrenCategories($request, $selectNode, $parent);
        } else {
            $grantedCategoryIds = $this->getGrantedCategories();

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
     * Get granted categories
     *
     * @return int[]
     */
    protected function getGrantedCategories()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return $this->categoryAccessRepo->getGrantedCategoryIds($user, Attributes::VIEW_ITEMS);
    }

    /**
     * Find a category from its id, trows an exception if not found or not granted
     *
     * @param int    $categoryId the category id
     * @param string $context    the retrieving context
     *
     * @throws AccessDeniedException
     *
     * @return Category
     */
    protected function findGrantedCategory($categoryId, $context)
    {
        $allowed = [self::CONTEXT_MANAGE, self::CONTEXT_VIEW, self::CONTEXT_ASSOCIATE];
        if (!in_array($context, $allowed)) {
            throw new AccessDeniedException('You can not access this category');
        }

        $category = $this->getCategory->byId($categoryId);

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
            $view = '@AkeneoPimEnrichment/CategoryTree/children.json.twig';
        } else {
            $view = '@AkeneoPimEnrichment/CategoryTree/children-tree.json.twig';
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

    public function removeAction(Request $request, $id)
    {
        try {
            return parent::removeAction($request, $id);
        } catch (PublishedProductConsistencyException $e) {
            return new JsonResponse([
                'message' => 'pimee_enrich.entity.category.flash.remove.error_linked',
            ], 422);
        }
    }
}

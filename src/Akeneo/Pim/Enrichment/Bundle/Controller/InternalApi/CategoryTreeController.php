<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Category\Domain\Model\Classification\CategoryTree;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\Query\GetCategoryTreesInterface;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
class CategoryTreeController extends AbstractController
{
    protected array $rawConfiguration;

    public function __construct(
        private UserContext $userContext,
        private CategoryRepositoryInterface $categoryRepository,
        private SecurityFacade $securityFacade,
        private GetCategoryInterface $getCategory,
        private GetCategoryTreesInterface $getCategoryTrees,
        private FeatureFlags $featureFlags,
        array $rawConfiguration,
    ) {
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
     * @return Response
     * @throws AccessDeniedException
     *
     */
    public function listTreeAction(Request $request): Response
    {
        if (false === $this->securityFacade->isGranted('pim_enrich_product_category_list')) {
            throw new AccessDeniedException();
        }

        $selectNodeId = $request->get('select_node_id', -1);

        $selectNode = $this->getCategory->byId($selectNodeId);
        if (!$selectNode) {
            $selectNode = $this->userContext->getUserCategoryTree($this->rawConfiguration['related_entity']);
        }

        $trees = $this->getCategoryTrees->getAll();

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
                'selected' => $tree->getId()?->getValue() === $selectedTreeId ? 'true' : 'false'
            ];
        }, $trees);

        return new JsonResponse($formatedTrees);
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
     * @return Response
     * @throws AccessDeniedException
     *
     */
    public function childrenAction(Request $request)
    {
        if (!$this->featureFlags->isEnabled('enriched_category')) {
            return $this->forward('pim_enrich.controller.category_tree.product_legacy::childrenAction', $request->query->all());
        }

        if (false === $this->securityFacade->isGranted('pim_enrich_product_category_list')) {
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
            $view = '@AkeneoPimEnrichment/CategoryTree/children.json.twig';
        } else {
            $view = '@AkeneoPimEnrichment/CategoryTree/children-tree.json.twig';
        }

        $withItemsCount = (bool)$request->get('with_items_count', false);
        $includeParent = (bool)$request->get('include_parent', false);
        $includeSub = (bool)$request->get('include_sub', false);

        return $this->render(
            $view,
            [
                'categories' => $categories,
                'parent' => ($includeParent) ? $parent : null,
                'include_sub' => $includeSub,
                'item_count' => $withItemsCount,
                'select_node' => $selectNode,
                'related_entity' => $this->rawConfiguration['related_entity']
            ],
            new JsonResponse()
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configure(OptionsResolver $resolver)
    {
        $resolver->setRequired(['related_entity', 'form_type', 'acl', 'route']);
    }
}

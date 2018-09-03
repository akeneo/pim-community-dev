<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Normalizer;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase\ListChildrenCategoriesWithCount;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase\ListChildrenCategoriesWithCountHandler;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase\ListRootCategoriesWithCount;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase\ListRootCategoriesWithCountHandler;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller to list the categories in the product grid.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductGridCategoryTreeController
{
    /** @var ListRootCategoriesWithCountHandler */
    private $listRootCategoriesWithCount;

    /** @var ListChildrenCategoriesWithCountHandler */
    private $listChildrenCategoriesWithCount;

    /** @var Normalizer\RootCategory */
    private $rootCategoryNormalizer;

    /** @var Normalizer\ChildCategory */
    private $childCategoryNormalizer;

    /** @var UserContext */
    private $userContext;

    /** @var SecurityFacade */
    private $securityFacade;

    /**
     * @param ListRootCategoriesWithCountHandler     $listRootCategoriesWithCount
     * @param ListChildrenCategoriesWithCountHandler $listChildrenCategoriesWithCount
     * @param Normalizer\RootCategory                $rootCategoryNormalizer
     * @param Normalizer\ChildCategory               $childCategoryNormalizer
     * @param UserContext                            $userContext
     * @param SecurityFacade                         $securityFacade
     */
    public function __construct(
        ListRootCategoriesWithCountHandler $listRootCategoriesWithCount,
        ListChildrenCategoriesWithCountHandler $listChildrenCategoriesWithCount,
        Normalizer\RootCategory $rootCategoryNormalizer,
        Normalizer\ChildCategory $childCategoryNormalizer,
        UserContext $userContext,
        SecurityFacade $securityFacade
    ) {
        $this->listRootCategoriesWithCount = $listRootCategoriesWithCount;
        $this->listChildrenCategoriesWithCount = $listChildrenCategoriesWithCount;
        $this->rootCategoryNormalizer = $rootCategoryNormalizer;
        $this->childCategoryNormalizer = $childCategoryNormalizer;
        $this->userContext = $userContext;
        $this->securityFacade = $securityFacade;
    }

    /**
     * The select_node_id is the id of the category selected as filter.
     * It allows to determine the selected tree where the category selected as filter belongs to.
     *
     * @param Request $request
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function listTreeAction(Request $request): Response
    {
        if (false === $this->securityFacade->isGranted('pim_enrich_product_category_list')) {
            throw new AccessDeniedException();
        }

        $user = $this->userContext->getUser();
        $translationLocale = $this->userContext->getCurrentLocale();

        $query = new ListRootCategoriesWithCount(
            $request->query->getInt('select_node_id', -1),
            $request->query->getBoolean('include_sub', false),
            $user->getId(),
            $translationLocale->getCode()
        );
        $rootCategories = $this->listRootCategoriesWithCount->handle($query);
        $normalizedData = $this->rootCategoryNormalizer->normalizeList($rootCategories);

        return new JsonResponse($normalizedData);
    }

    /**
     * List children of a category.
     *
     * The category to expand is provided via its id ('id' request parameter).
     * The category selected as filter is given by 'select_node_id' request parameter.
     *
     * If the category selected as filter is a direct child of the category to expand, the tree
     * is expanded until the category selected as filter is found among the children.
     *
     * @param Request $request
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function listChildrenAction(Request $request): Response
    {
        if (false === $this->securityFacade->isGranted('pim_enrich_product_category_list')) {
            throw new AccessDeniedException();
        }

        $user = $this->userContext->getUser();
        $translationLocale = $this->userContext->getCurrentLocale();

        $query = new ListChildrenCategoriesWithCount(
            $request->query->getInt('id', -1),
            $request->query->getInt('select_node_id', -1),
            $request->query->getBoolean('include_sub', false),
            $user->getId(),
            $translationLocale->getCode()
        );

        $categories = $this->listChildrenCategoriesWithCount->handle($query);
        $normalizedData = $this->childCategoryNormalizer->normalizeList($categories);

        return new JsonResponse($normalizedData);
    }
}

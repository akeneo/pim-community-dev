<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Application\Query\GetCategory\InternalApiCategory;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Infrastructure\Registry\FindCategoryAdditionalPropertiesRegistry;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryController
{
    public function __construct(
        private SecurityFacade $securityFacade,
        private GetCategoryInterface $getCategory,
        private FindCategoryAdditionalPropertiesRegistry $findCategoryAdditionalPropertiesRegistry,
    ) {
    }

    public function __invoke(Request $request, int $id): Response
    {
        if ($this->securityFacade->isGranted('pim_enrich_product_category_edit') === false) {
            // even if this is a read endpoint, the user must be granted edition rights
            // as this should only be used in the purpose of updating a category from the UI
            throw new AccessDeniedException();
        }

        $category = $this->getCategory->byId($id);
        $category = $this->findCategoryAdditionalPropertiesRegistry->forCategory($category);

        $isRoot = $category->isRoot();
        $root = null;
        if (!$isRoot) {
            $root = $this->getCategory->byId($category->getRootId()?->getValue());
            $root = $this->findCategoryAdditionalPropertiesRegistry->forCategory($root);
        }

        $internalApiCategory = InternalApiCategory::fromCategory($category, $root);
        $response = $internalApiCategory->normalize();

        return new JsonResponse($response, Response::HTTP_OK);
    }
}

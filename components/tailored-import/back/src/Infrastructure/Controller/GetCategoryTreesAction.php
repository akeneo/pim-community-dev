<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Controller;

use Akeneo\Pim\Enrichment\Component\Category\Query\PublicApi\CategoryTree;
use Akeneo\Pim\Enrichment\Component\Category\Query\PublicApi\FindCategoryTrees;
use Akeneo\Pim\Enrichment\Component\Category\Query\PublicApi\GetCategoryChildrenCodesPerTreeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetCategoryTreesAction
{
    public function __construct(
        private FindCategoryTrees $findCategoryTrees,
        private GetCategoryChildrenCodesPerTreeInterface $getCategoryChildrenCodesPerTree,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $categoryCodesWithError = $request->get('category_codes_with_error', []);
        $trees = $this->findCategoryTrees->execute();

        return new JsonResponse($this->normalizeCategoryTrees($trees, $categoryCodesWithError));
    }

    private function normalizeCategoryTrees(array $trees, array $categoryCodesWithError): array
    {
        $categoriesWithErrorPerTree = $this->getCategoryChildrenCodesPerTree->executeWithoutChildren($categoryCodesWithError);

        return array_map(
            static function (CategoryTree $categoryTree) use ($categoriesWithErrorPerTree) {
                $result = $categoryTree->normalize();
                $result['has_error'] = !empty($categoriesWithErrorPerTree[$categoryTree->code]);

                return $result;
            },
            $trees
        );
    }
}

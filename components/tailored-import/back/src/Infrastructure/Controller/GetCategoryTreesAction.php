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

use Akeneo\Pim\Enrichment\Component\Category\Query\PublicApi\FindCategoryTrees;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetCategoryTreesAction
{
    public function __construct(
        private FindCategoryTrees $findCategoryTrees,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $trees = $this->findCategoryTrees->execute();
        $normalizedTrees = array_map(static fn ($tree) => $tree->normalize(), $trees);

        return new JsonResponse($normalizedTrees);
    }
}

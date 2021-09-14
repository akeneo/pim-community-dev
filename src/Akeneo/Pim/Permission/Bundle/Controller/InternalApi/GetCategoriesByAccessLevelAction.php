<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Controller\InternalApi;

use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetRootCategoryCodeAndLabelByAccessLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class GetCategoriesByAccessLevelAction
{
    private GetRootCategoryCodeAndLabelByAccessLevel $getCategoriesByAccessLevel;

    public function __construct(GetRootCategoryCodeAndLabelByAccessLevel $getCategoriesByAccessLevel)
    {
        $this->getCategoriesByAccessLevel = $getCategoriesByAccessLevel;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        // router + chemin absolue
        $route = $request->get('_route');
        $offset = $request->get('offset');
        $limit = $request->get('limit');
dump($route, $offset, $limit);

        return new JsonResponse();
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Controller\InternalApi;

use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetRootCategoryCodesAndLabels;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
class GetCategoriesForPermissionsAction
{
    private GetRootCategoryCodesAndLabels $getRootCategoryCodesAndLabels;
    private RouterInterface $router;

    public function __construct(
        GetRootCategoryCodesAndLabels $getRootCategoryCodesAndLabels,
        RouterInterface $router
    ) {
        $this->getRootCategoryCodesAndLabels = $getRootCategoryCodesAndLabels;
        $this->router = $router;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $locale = (string) $request->get('ui_locale', 'en_US');
        $search = (string) $request->get('search', '');
        $offset = (int) $request->get('offset', 0);
        $limit = (int) $request->get('limit', 100);

        $results = $this->getRootCategoryCodesAndLabels->execute($locale, $search, $offset, $limit);

        $next = count($results) < $limit
            ? null
            : $this->router->generate('pimee_permissions_entities_get_categories', [
                'ui_locale' => $locale,
                'search' => $search,
                'offset' => $offset + $limit,
                'limit' => $limit,
            ], RouterInterface::ABSOLUTE_URL);

        return new JsonResponse([
            'next' => [
                'url' => $next,
                'params' => [
                    'ui_locale' => $locale,
                    'search' => $search,
                    'offset' => $offset + $limit,
                    'limit' => $limit,
                ],
            ],
            'results' => $results,
        ]);
    }
}

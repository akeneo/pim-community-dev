<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Controller\InternalApi;

use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\AttributeGroup\GetAttributeGroupCodesAndLabels;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class GetAttributeGroupsForPermissionsAction
{
    private GetAttributeGroupCodesAndLabels $getAttributeGroupQuery;
    private RouterInterface $router;

    public function __construct(
        GetAttributeGroupCodesAndLabels $getAttributeGroupQuery,
        RouterInterface $router
    ) {
        $this->getAttributeGroupQuery = $getAttributeGroupQuery;
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

        $results = $this->getAttributeGroupQuery->execute($locale, $search, $offset, $limit);

        $next = count($results) < $limit
            ? null
            : $this->router->generate('pimee_permissions_entities_get_attribute_groups', [
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

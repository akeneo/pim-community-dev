<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Bundle\Controller\InternalApi;

use Akeneo\Pim\Permission\Bundle\Controller\InternalApi\GetAttributeGroupsForPermissionsAction;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\AttributeGroup\GetAttributeGroupCodesAndLabels;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class GetAttributeGroupsForPermissionsActionSpec extends ObjectBehavior
{
    public function let(GetAttributeGroupCodesAndLabels $query, RouterInterface $router): void
    {
        $this->beConstructedWith($query, $router);
    }

    public function it_is_a_get_attribute_groups_action(): void
    {
        $this->shouldHaveType(GetAttributeGroupsForPermissionsAction::class);
    }

    public function it_returns_the_next_url_with_parameters_if_there_is_more_results_to_expect(
        GetAttributeGroupCodesAndLabels $query,
        RouterInterface $router
    ): void {
        $request = new Request([
            'ui_locale' => 'en_US',
            'search' => 'jambon',
            'limit' => 1,
        ]);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $queryResults = [
            [
                'code' => 'marketingjambon',
                'label' => 'Marketing',
            ],
            [
                'code' => 'technical',
                'label' => 'Technijamboncal',
            ],
        ];
        $query->execute('en_US', 'jambon', 0, 1)->willReturn($queryResults);
        $router->generate(
            'pimee_permissions_entities_get_attribute_groups',
            [
                'ui_locale' => 'en_US',
                'search' => 'jambon',
                'offset' => 1,
                'limit' => 1,
            ],
            RouterInterface::ABSOLUTE_URL
        )->willReturn(
            'url.test/rest/permissions/attribute-group?limit=1&offset=1&search=jambon&ui_locale=en_US'
        );
        $this->__invoke($request)->shouldBeLike(new JsonResponse([
            'next' => [
                'url' => 'url.test/rest/permissions/attribute-group?limit=1&offset=1&search=jambon&ui_locale=en_US',
                'params' => [
                    'ui_locale' => 'en_US',
                    'search' => 'jambon',
                    'offset' => 1,
                    'limit' => 1,
                ],
            ],
            'results' => $queryResults,
        ]));
    }

    public function it_oes_not_returns_the_next_url_if_there_is_no_more_results_to_expect(
        GetAttributeGroupCodesAndLabels $query
    ): void {
        $request = new Request([
            'ui_locale' => 'en_US',
            'search' => 'jambon'
        ]);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $queryResults = [
            [
                'code' => 'markejambonting',
                'label' => 'Marketing',
            ],
            [
                'code' => 'technical',
                'label' => 'Technical jambon',
            ],
        ];
        $query->execute('en_US', 'jambon', 0, 100)->willReturn($queryResults);
        $this->__invoke($request)->shouldBeLike(new JsonResponse([
            'next' => [
                'url' => null,
                'params' => [
                    'ui_locale' => 'en_US',
                    'search' => 'jambon',
                    'offset' => 100,
                    'limit' => 100,
                ],
            ],
            'results' => $queryResults,
        ]));
    }
}

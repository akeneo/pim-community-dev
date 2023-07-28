<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\Controller\ExternalApi;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class FakeListLocalesController
{
    public function __construct() {
    }

    #[OA\Get(
        path: '/api/rest/v1/fake-locales',
        operationId: 'get_fake_locales',
        description: 'This endpoint allows you to get a list of locales. Locales are paginated and sorted by code.',
        summary: 'Get a list of locales',
        security: [
            ['bearerToken' => []],
        ],
        tags: ['Locale'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                description: 'Filter locales. For now, only the `enabled` filter is available.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    example: '{"enabled":[{"operator":"=","value":true}]}'
                )
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page number',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            ),
            new OA\Parameter(
                name: 'limit',
                description: 'Maximum number of items per page',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 10
                )
            ),
            new OA\Parameter(
                name: 'with_count',
                description: 'Whether or not to count the total of items',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'boolean',
                    example: false
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Return locales paginated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: '_links',
                            ref: '#/components/schemas/_links',
                        ),
                        new OA\Property(
                            property: 'current_page',
                            description: 'Current page number',
                            type: 'integer',
                            example: 1
                        ),
                        new OA\Property(
                            property: '_embedded',
                            ref: '#/components/schemas/_embedded_locale'
                        )
                    ],
                    type: 'object',
                )
            ),
            new OA\Response(
                ref: '#/components/responses/401',
                response: '401'
            ),
            new OA\Response(
                ref: '#/components/responses/403',
                response: '403'
            ),
            new OA\Response(
                ref: '#/components/responses/406',
                response: '406'
            ),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        return new JsonResponse();
    }
}

<?php

namespace Akeneo\Tool\Bundle\ApiBundle;

use OpenApi\Attributes as OA;

/**
 * URL API Documentation
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[OA\Info(
    version: '1.0.0',
    description: 'The Akeneo REST API brought to you!\n\nFind out how this Postman collection works by visiting https://api.akeneo.com',
    title: 'Official Akeneo PIM REST API Serenity',
)]
#[OA\OpenApi(
    x: [
        'readme' => [
            'samples-languages' => [
                'curl',
                'php',
                'javascript',
                'node',
                'python',
                'java',
                'csharp',
            ],
        ],
    ]
)]
#[OA\Server(
    url: '{protocol}://{sandboxName}',
    description: 'Your Sandbox',
    variables: [
        new OA\ServerVariable(
            serverVariable: 'protocol',
            default: 'https',
            enum: ['http', 'https']
        ),
        new OA\ServerVariable(
            serverVariable: 'sandboxName',
            default: 'your-sandbox.demo.cloud.akeneo.com'
        ),
    ]
)]
#[OA\Components(
    schemas: [
        new OA\Schema(
            schema: '_links',
            properties: [
                new OA\Property(
                    property: 'self',
                    properties: [
                        new OA\Property(
                            property: 'href',
                            description: 'URI of the current page of resources',
                            type: 'string'
                        )
                    ],
                    type: 'object',
                ),
                new OA\Property(
                    property: 'first',
                    properties: [
                        new OA\Property(
                            property: 'href',
                            description: 'URI of the first page of resources',
                            type: 'string'
                        )
                    ],
                    type: 'object',
                ),
                new OA\Property(
                    property: 'previous',
                    properties: [
                        new OA\Property(
                            property: 'href',
                            description: 'URI of the previous page of resources',
                            type: 'string'
                        )
                    ],
                    type: 'object',
                ),
                new OA\Property(
                    property: 'new',
                    properties: [
                        new OA\Property(
                            property: 'href',
                            description: 'URI of the next page of resources',
                            type: 'string'
                        )
                    ],
                    type: 'object',
                ),
            ],
            type: 'object'
        ),
    ],
    responses: [
        new OA\Response(
            response: 401,
            description: 'Authentication required',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'code',
                        description: 'The HTTP status code',
                        type: 'integer',
                        example: 401,
                    ),
                    new OA\Property(
                        property: 'message',
                        description: 'A message explaining the error',
                        type: 'string',
                        example: 'Authentication is required'
                    )
                    ],
                type: 'object',
            ),
            x: ['details' => 'Can be caused by a missing or expired token.'],
        ),
        new OA\Response(
            response: 403,
            description: 'Access forbidden',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'code',
                        description: 'The HTTP status code',
                        type: 'integer',
                        example: 403,
                    ),
                    new OA\Property(
                        property: 'message',
                        description: 'A message explaining the error',
                        type: 'string',
                        example: 'Access forbidden. You are not allowed to list categories.'
                    )
                ],
                type: 'object',
            ),
            x: ['details' => 'The user does not have the permission to execute this request.'],
        ),
        new OA\Response(
            response: 406,
            description: 'Not Acceptable',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'code',
                        description: 'The HTTP status code',
                        type: 'integer',
                        example: 406,
                    ),
                    new OA\Property(
                        property: 'message',
                        description: 'A message explaining the error',
                        type: 'string',
                        example: '‘xxx’ in ‘Accept‘ header is not valid. Only ‘application/json‘ is allowed.'
                    )
                ],
                type: 'object',
            ),
            x: ['details' => 'The `Accept` header is not `application/json` but it should.'],
        ),
        new OA\Response(
            response: 422,
            description: 'Unprocessable entity',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'code',
                        description: 'The HTTP status code',
                        type: 'integer',
                        example: 422,
                    ),
                    new OA\Property(
                        property: 'message',
                        description: 'A message explaining the error',
                        type: 'string',
                        example: 'Property "labels" expects an array as data, "NULL" given. Check the API reference documentation.'
                    )
                ],
                type: 'object',
            ),
            x: ['details' => 'The validation of the entity given in the body of the request failed'],
        ),
    ],
    securitySchemes: [
        new OA\SecurityScheme(
            securityScheme: 'bearerToken',
            type: 'http',
            scheme: 'bearer',
    ),
        new OA\SecurityScheme(
            securityScheme: 'basicToken',
            type: 'http',
            scheme: 'basic',
    ),
    ]
)]
class Documentation
{
    const URL = 'http://api.akeneo.com/api-reference.html#';
    const URL_DOCUMENTATION = 'http://api.akeneo.com/documentation/';
}

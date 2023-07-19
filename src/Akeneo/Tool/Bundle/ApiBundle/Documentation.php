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
                    property: 'next',
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
        new OA\Schema(
            schema: '_embedded',
            properties: [
                new OA\Property(
                    property: 'items',
                    description: 'The list of resources',
                    type: 'array',
                    items: new OA\Items(
                        ref: '#/components/schemas/Product'
                    ),
                    example: [
                                [
                                    '_links' => [
                                        'self' => [
                                            'href' => 'http://host/api/rest/v1/products/akeneo_tshirt',
                                        ],
                                    ],
                                    'identifier' => 'akeneo_tshirt',
                                    'family' => 'tshirts',
                                    'parent' => null,
                                    'groups' => [],
                                    'categories' => [],
                                    'enabled' => true,
                                    'values' => [
                                        'name' => [
                                            [
                                                'locale' => null,
                                                'scope' => null,
                                                'data' => 'Akeno T-Shirt',
                                            ],
                                        ],
                                        'description' => [
                                            [
                                                'locale' => 'en_US',
                                                'scope' => null,
                                                'data' => 'A really nice t-shirt',
                                            ],
                                        ],
                                        'price' => [
                                            [
                                                'locale' => null,
                                                'scope' => 'ecommerce',
                                                'data' => [
                                                    'amount' => 10,
                                                    'currency' => 'EUR',
                                                ],
                                            ],
                                        ],
                                    ],
                                    'created' => '2017-03-06T13:23:23+01:00',
                                    'updated' => '2017-03-06T13:23:23+01:00',
                                    'associations' => [],
                                    'quantified_associations' => [],
                                    'metadata' => [
                                        'id' => 1,
                                        'form' => 'pim-product-edit-form',
                                    ],
                                ],
                                [
                                    '_links' => [
                                        'self' => [
                                            'href' => 'http://host/api/rest/v1/products/akeneo_pant',
                                        ],
                                    ],
                                    'identifier' => 'akeneo_pant',
                                    'family' => 'tshirts',
                                    'parent' => null,
                                    'groups' => [],
                                    'categories' => [],
                                    'enabled' => true,
                                    'values' => [
                                        'name' => [
                                            [
                                                'locale' => null,
                                                'scope' => null,
                                                'data' => 'Akeno Pant',
                                            ],
                                        ],
                                        'description' => [
                                            [
                                                'locale' => 'en_US',
                                                'scope' => null,
                                                'data' => 'A really nice pant',
                                            ],
                                        ],
                                    ],
                                ],
                        ],
                )
            ],
            type: 'object',
        ),
        new OA\Schema(
            schema: 'Category_partial_update_list_request_body',
            required: ['code'],
            properties: [
                new OA\Property(
                    property: 'code',
                    description: 'Category code',
                    type: 'string',
                    example: 'woman_coat',
                    x: ['immutable' => true,]
                ),
                new OA\Property(
                    property: 'parent',
                    description: 'Category code of the parent\'s category.',
                    type: 'string',
                    default: null,
                    example: 'winter_collection',
                    x: ['validation-rules' => '&bull; It is either equal to `null` or to an existing category code. &#10;&bull; If equal to an existing category code, it cannot reference itself.',]
                ),
                new OA\Property(
                    property: 'updated',
                    description: 'Date of the last update',
                    type: 'string',
                    format: 'dateTime',
                    example: '2021-05-22T12:48:00+02:00',
                    x: ['read-only' => true,]
                ),
                new OA\Property(
                    property: 'position',
                    description: 'Position of the category in its level, start from 1 (only available since the 7.0 version and when query parameter \"with_position\" is set to \"true\")',
                    type: 'integer',
                    example: 1,
                    x: ['read-only' => true,]
                ),
                new OA\Property(
                    property: 'labels',
                    description: 'Category labels for each locale',
                    properties: [
                        new OA\Property(
                            property: 'localeCode',
                            description: 'Category label for the locale `localeCode',
                            type: 'string',
                            example: 'en_US',
                        ),
                        new OA\Property(
                            property: 'Category label.',
                            description: 'Category label for the locale `localeCode',
                            type: 'string',
                            example: 'Winter_collection',
                        ),
                    ],
                    type: 'object',
                    default: [],
                    x: ['validation-rules' => 'The `localeCode` is the code of an existing and activated locale',]
                ),
            ],
            type: 'object',
            example:
                [
                "code"=> "winter_collection",
                "parent"=> null,
                "updated"=> "2021-05-22T12:48:00+02:00",
                "position"=> 1,
                "labels"=> [
                    "en_US"=> "Winter collection",
                    "fr_FR"=> "Collection hiver"
                    ]
            ],
        ),
        new OA\Schema(
            schema: 'Category_partial_update_list_response_body',
            properties: [
                new OA\Property(
                    property: 'lines',
                    description: 'Line number',
                    type: 'integer',
                ),
                new OA\Property(
                    property: 'identifier',
                    description: 'Resource identifier, only filled when the resource is a product',
                    type: 'string',
                ),
                new OA\Property(
                    property: 'code',
                    description: 'Resource code, only filled when the resource is not a product',
                    type: 'string',
                ),
                new OA\Property(
                    property: 'status_code',
                    description: 'HTTP status code, see <a href=\"/documentation/responses.html#client-errors\">Client errors</a> to understand the meaning of each code',
                    type: 'integer',
                ),
                new OA\Property(
                    property: 'message',
                    description: 'Message explaining the error',
                    type: 'string',
                ),
            ],
            type: 'object',
            example: [
                [
                    'lines' => 1,
                    'code' => 'winter_collection',
                    'status_code' => 201,
                ],
                [
                    'lines' => 2,
                    'code' => 'woman',
                    'status_code' => 422,
                    'message' => 'Category "spring_collection" does not exist.',
                ],
            ],
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
            response: 413,
            description: 'Request Entity Too Large',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'code',
                        description: 'The HTTP status code',
                        type: 'integer',
                        example: 413,
                    ),
                    new OA\Property(
                        property: 'message',
                        description: 'A message explaining the error',
                        type: 'string',
                        example: 'Too many resources to process, 100 is the maximum allowed.'
                    )
                ],
                type: 'object',
            ),
            x: ['details' => 'There are too many resources to process (max 100) or the line of JSON is too long (max 1 000 000 characters).'],
        ),
        new OA\Response(
            response: 415,
            description: 'Unsupported Media type',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'code',
                        description: 'The HTTP status code',
                        type: 'integer',
                        example: 415,
                    ),
                    new OA\Property(
                        property: 'message',
                        description: 'A message explaining the error',
                        type: 'string',
                        example: '‘xxx’ in ‘Content-type’ header is not valid.  Only ‘application/vnd.akeneo.collection+json’ is allowed.'
                    )
                ],
                type: 'object',
            ),
            x: ['details' => 'The `Content-type` header has to be `application/vnd.akeneo.collection+json`.'],
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

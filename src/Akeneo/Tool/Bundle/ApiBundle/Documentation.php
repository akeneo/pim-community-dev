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
)]
class Documentation
{
    const URL = 'http://api.akeneo.com/api-reference.html#';
    const URL_DOCUMENTATION = 'http://api.akeneo.com/documentation/';
}

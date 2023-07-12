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
    responses: [
        new OA\Response(
            response: 401,
            description: 'Authentication required',
            content: new OA\JsonContent(
                description: 'An error response object',
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
            x: ['details' => 'Can be caused by a missing or expired token'],
        )
    ],
    securitySchemes: [
        new OA\SecurityScheme(
        securityScheme: 'oAuthSample',
        type: 'http',
        scheme: 'bearer',
    ),
    new OA\SecurityScheme(
        securityScheme: 'bearerAuth',
        type: 'apiKey',
        description: 'TODO: un lien externe pour calculer cette merde ?',
        name: 'Authorization',
        in: 'header'
    ),
    new OA\SecurityScheme(
        securityScheme: 'basicAuth',
        type: 'http',
        description: 'Use `clientid` / `secret` as the test credentials',
        name: 'clientid',
        in: 'header',
        scheme: 'basic',
    ),
    ]
)]
class Documentation
{
    const URL = 'http://api.akeneo.com/api-reference.html#';
    const URL_DOCUMENTATION = 'http://api.akeneo.com/documentation/';
}

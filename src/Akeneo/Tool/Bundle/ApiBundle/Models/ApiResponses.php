<?php

namespace Akeneo\Tool\Bundle\ApiBundle\Models;

use OpenApi\Attributes as OA;

#[OA\Response(
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
)]
#[OA\Response(
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
)]
#[OA\Response(
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
)]
#[OA\Response(
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
)]
#[OA\Response(
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
)]
#[OA\Response(
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
)]
class ApiResponses
{

}
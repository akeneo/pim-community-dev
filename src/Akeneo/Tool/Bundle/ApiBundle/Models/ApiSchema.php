<?php

namespace Akeneo\Tool\Bundle\ApiBundle\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
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
)]
#[OA\Schema(
    schema: '_embedded_product',
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
)]
#[OA\Schema(
    schema: '_embedded_locale',
    properties: [
        new OA\Property(
            property: 'items',
            description: 'The list of resources',
            type: 'array',
            items: new OA\Items(
                ref: '#/components/schemas/Locale'
            ),
            example: [
                [
                    '_links' => [
                        'self' => [
                            'href' => 'https://demo.akeneo.com/api/rest/v1/locales/en_US"',
                        ],
                    ],
                    'code' => 'en_US',
                    'enabled' => 'true',
                ],
                [
                    '_links' => [
                        'self' => [
                            'href' => 'https://demo.akeneo.com/api/rest/v1/locales/fr_FR',
                        ],
                    ],
                    'code' => 'fr_FR',
                    'enabled' => 'false',

                ],
            ],
        )
    ],
    type: 'object',
)]
class ApiSchema
{

}
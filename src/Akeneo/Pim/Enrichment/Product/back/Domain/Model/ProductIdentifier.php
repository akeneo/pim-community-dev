<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\Model;

use OpenApi\Attributes as OA;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
// generate schema for product
#[OA\Schema(
    schema: 'Product',
    properties: [
        new OA\Property(
            property: 'identifier',
            type: 'string',
        ),
        new OA\Property(
            property: 'family',
            type: 'string',
        ),
        new OA\Property(
            property: 'enabled',
            type: 'boolean',
        ),
        new OA\Property(
            property: 'categories',
            type: 'array',
            items: new OA\Items(type: 'string'),
        ),
        new OA\Property(
            property: 'values',
            properties: [
                new OA\Property(
                    ref: '#/components/schemas/ProductValue',
                    type: 'object',
                ),
                ],
            type: 'object',
        ),
        new OA\Property(
            property: 'associations',
            properties: [
                new OA\Property(
                    ref: '#/components/schemas/ProductAssociation',
                    type: 'object',
            ),
                ],
            type: 'object',
        ),
        new OA\Property(
            property: 'created',
            type: 'string',
            format: 'date-time',
        ),
        new OA\Property(
            property: 'updated',
            type: 'string',
            format: 'date-time',
        ),
    ],
)]
// generate schema for product value
#[OA\Schema(
    schema: 'ProductValue',
    properties: [
        new OA\Property(
            property: 'locale',
            type: 'string',
        ),
        new OA\Property(
            property: 'scope',
            type: 'string',
        ),
        new OA\Property(
            property: 'data',
            type: 'string',
        ),
    ],
)]
// generate schema for product association
#[OA\Schema(
    schema: 'ProductAssociation',
    properties: [
        new OA\Property(
            property: 'association_type',
            type: 'string',
        ),
        new OA\Property(
            property: 'products',
            type: 'array',
            items: new OA\Items(type: 'string'),
        ),
        new OA\Property(
            property: 'product_models',
            type: 'array',
            items: new OA\Items(type: 'string'),
        ),
        new OA\Property(
            property: 'groups',
            type: 'array',
            items: new OA\Items(type: 'string'),
        ),
    ],
)]
final class ProductIdentifier
{
    private function __construct(private string $identifier)
    {
        Assert::NotEmpty($this->identifier);
    }

    public static function fromString(string $identifier): ProductIdentifier
    {
        return new self($identifier);
    }

    public function asString(): string
    {
        return $this->identifier;
    }
}

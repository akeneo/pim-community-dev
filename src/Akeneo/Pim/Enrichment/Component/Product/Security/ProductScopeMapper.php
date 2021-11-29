<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductScopeMapper implements ScopeMapperInterface
{
    private const SCOPE_READ_PRODUCTS = 'read_products';
    private const SCOPE_WRITE_PRODUCTS = 'write_products';
    private const SCOPE_DELETE_PRODUCTS = 'delete_products';

    private const SCOPE_ACL_MAP = [
        self::SCOPE_READ_PRODUCTS => [
            'pim_api_product_list',
        ],
        self::SCOPE_WRITE_PRODUCTS => [
            'pim_api_product_edit',
        ],
        self::SCOPE_DELETE_PRODUCTS => [
            'pim_api_product_remove',
        ],
    ];

    private const SCOPE_MESSAGE_MAP = [
        self::SCOPE_READ_PRODUCTS => [
            'icon' => 'products',
            'type' => 'view',
            'entities' => 'products',
        ],
        self::SCOPE_WRITE_PRODUCTS => [
            'icon' => 'products',
            'type' => 'edit',
            'entities' => 'products',
        ],
        self::SCOPE_DELETE_PRODUCTS => [
            'icon' => 'products',
            'type' => 'delete',
            'entities' => 'products',
        ],
    ];

    private const SCOPE_HIERARCHY = [
        self::SCOPE_WRITE_PRODUCTS => [
            self::SCOPE_READ_PRODUCTS,
        ],
        self::SCOPE_DELETE_PRODUCTS => [
            self::SCOPE_READ_PRODUCTS,
            self::SCOPE_WRITE_PRODUCTS,
        ],
    ];

    public function getScopes(): array
    {
        return [
            self::SCOPE_READ_PRODUCTS,
            self::SCOPE_WRITE_PRODUCTS,
            self::SCOPE_DELETE_PRODUCTS,
        ];
    }

    public function getAcls(string $scopeName): array
    {
        if (!\array_key_exists($scopeName, self::SCOPE_ACL_MAP)) {
            return [];
        }

        return self::SCOPE_ACL_MAP[$scopeName];
    }

    public function getMessage(string $scopeName): array
    {
        if (!\array_key_exists($scopeName, self::SCOPE_MESSAGE_MAP)) {
            return [];
        }

        return self::SCOPE_MESSAGE_MAP[$scopeName];
    }

    public function getLowerHierarchyScopes(string $scopeName): array
    {
        if (!\array_key_exists($scopeName, self::SCOPE_HIERARCHY)) {
            return [];
        }

        return self::SCOPE_HIERARCHY[$scopeName];
    }
}

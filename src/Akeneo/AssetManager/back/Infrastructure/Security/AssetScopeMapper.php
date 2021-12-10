<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
class AssetScopeMapper implements ScopeMapperInterface
{
    private const SCOPE_WRITE_ASSET_FAMILIES = 'write_asset_families';
    private const SCOPE_READ_ASSET_FAMILIES = 'read_asset_families';
    private const SCOPE_WRITE_ASSETS = 'write_assets';
    private const SCOPE_READ_ASSETS = 'read_assets';
    private const SCOPE_DELETE_ASSETS = 'delete_assets';

    private const SCOPE_ACL_MAP = [
        self::SCOPE_WRITE_ASSET_FAMILIES => [
            'pim_api_asset_family_edit'
        ],
        self::SCOPE_READ_ASSET_FAMILIES => [
            'pim_api_asset_family_list'
        ],
        self::SCOPE_WRITE_ASSETS => [
            'pim_api_asset_edit'
        ],
        self::SCOPE_READ_ASSETS => [
            'pim_api_asset_list'
        ],
        self::SCOPE_DELETE_ASSETS => [
            'pim_api_asset_remove'
        ],
    ];

    private const SCOPE_MESSAGE_MAP = [
        self::SCOPE_WRITE_ASSET_FAMILIES => [
            'icon' => 'asset_families',
            'type' => 'edit',
            'entities' => 'asset_families',
        ],
        self::SCOPE_READ_ASSET_FAMILIES => [
            'icon' => 'asset_families',
            'type' => 'view',
            'entities' => 'asset_families',
        ],
        self::SCOPE_WRITE_ASSETS => [
            'icon' => 'assets',
            'type' => 'edit',
            'entities' => 'assets',
        ],
        self::SCOPE_READ_ASSETS => [
            'icon' => 'assets',
            'type' => 'view',
            'entities' => 'assets',
        ],
        self::SCOPE_DELETE_ASSETS => [
            'icon' => 'assets',
            'type' => 'delete',
            'entities' => 'assets',
        ],
    ];

    private const SCOPE_HIERARCHY = [
        self::SCOPE_WRITE_ASSET_FAMILIES => [
            self::SCOPE_READ_ASSET_FAMILIES,
        ],
        self::SCOPE_WRITE_ASSETS => [
            self::SCOPE_READ_ASSETS,
        ],
        self::SCOPE_DELETE_ASSETS => [
            self::SCOPE_WRITE_ASSETS,
            self::SCOPE_READ_ASSETS,
        ],
    ];

    public function getScopes(): array
    {
        return [
            self::SCOPE_WRITE_ASSET_FAMILIES,
            self::SCOPE_READ_ASSET_FAMILIES,
            self::SCOPE_WRITE_ASSETS,
            self::SCOPE_READ_ASSETS,
            self::SCOPE_DELETE_ASSETS,
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

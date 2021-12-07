<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CatalogStructureScopeMapper implements ScopeMapperInterface
{
    private const SCOPE_READ_CATALOG_STRUCTURE = 'read_catalog_structure';
    private const SCOPE_WRITE_CATALOG_STRUCTURE = 'write_catalog_structure';

    private const SCOPE_ACL_MAP = [
        self::SCOPE_READ_CATALOG_STRUCTURE => [
            'pim_api_attribute_list',
            'pim_api_attribute_group_list',
            'pim_api_family_list',
            'pim_api_family_variant_list',
        ],
        self::SCOPE_WRITE_CATALOG_STRUCTURE => [
            'pim_api_attribute_edit',
            'pim_api_attribute_group_edit',
            'pim_api_family_edit',
            'pim_api_family_variant_edit',
        ],
    ];

    private const SCOPE_MESSAGE_MAP = [
        self::SCOPE_READ_CATALOG_STRUCTURE => [
            'icon' => 'catalog_structure',
            'type' => 'view',
            'entities' => 'catalog_structure',
        ],
        self::SCOPE_WRITE_CATALOG_STRUCTURE => [
            'icon' => 'catalog_structure',
            'type' => 'edit',
            'entities' => 'catalog_structure',
        ],
    ];

    private const SCOPE_HIERARCHY = [
        self::SCOPE_READ_CATALOG_STRUCTURE => [],
        self::SCOPE_WRITE_CATALOG_STRUCTURE => [
            self::SCOPE_READ_CATALOG_STRUCTURE,
        ],
    ];

    public function getScopes(): array
    {
        return [
            self::SCOPE_READ_CATALOG_STRUCTURE,
            self::SCOPE_WRITE_CATALOG_STRUCTURE,
        ];
    }

    public function getAcls(string $scopeName): array
    {
        if (!\array_key_exists($scopeName, self::SCOPE_ACL_MAP)) {
            throw new \InvalidArgumentException(sprintf('The scope "%s" does not exist.', $scopeName));
        }

        return self::SCOPE_ACL_MAP[$scopeName];
    }

    public function getMessage(string $scopeName): array
    {
        if (!\array_key_exists($scopeName, self::SCOPE_MESSAGE_MAP)) {
            throw new \InvalidArgumentException(sprintf('The scope "%s" does not exist.', $scopeName));
        }

        return self::SCOPE_MESSAGE_MAP[$scopeName];
    }

    public function getLowerHierarchyScopes(string $scopeName): array
    {
        if (!\array_key_exists($scopeName, self::SCOPE_HIERARCHY)) {
            throw new \InvalidArgumentException(sprintf('The scope "%s" does not exist.', $scopeName));
        }

        return self::SCOPE_HIERARCHY[$scopeName];
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeScopeMapper implements ScopeMapperInterface
{
    private const SCOPE_READ_ASSOCIATION_TYPES = 'read_association_types';
    private const SCOPE_WRITE_ASSOCIATION_TYPES = 'write_association_types';

    private const SCOPE_ACL_MAP = [
        self::SCOPE_READ_ASSOCIATION_TYPES => [
            'pim_api_association_type_list',
        ],
        self::SCOPE_WRITE_ASSOCIATION_TYPES => [
            'pim_api_association_type_edit',
        ],
    ];

    private const SCOPE_MESSAGE_MAP = [
        self::SCOPE_READ_ASSOCIATION_TYPES => [
            'icon' => 'association_types',
            'type' => 'view',
            'entities' => 'association_types',
        ],
        self::SCOPE_WRITE_ASSOCIATION_TYPES => [
            'icon' => 'association_types',
            'type' => 'edit',
            'entities' => 'association_types',
        ],
    ];

    private const SCOPE_HIERARCHY = [
        self::SCOPE_WRITE_ASSOCIATION_TYPES => [
            self::SCOPE_READ_ASSOCIATION_TYPES,
        ],
    ];

    public function getScopes(): array
    {
        return [
            self::SCOPE_READ_ASSOCIATION_TYPES,
            self::SCOPE_WRITE_ASSOCIATION_TYPES,
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

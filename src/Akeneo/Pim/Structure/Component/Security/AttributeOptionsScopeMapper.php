<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Security;

use Akeneo\Tool\Component\Api\Security\ScopeMapperInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionsScopeMapper implements ScopeMapperInterface
{
    private const SCOPE_READ_ATTRIBUTE_OPTIONS = 'read_attribute_options';
    private const SCOPE_WRITE_ATTRIBUTE_OPTIONS = 'write_attribute_options';

    private const SCOPE_ACL_MAP = [
        self::SCOPE_READ_ATTRIBUTE_OPTIONS => [
            'pim_api_attribute_options_list',
        ],
        self::SCOPE_WRITE_ATTRIBUTE_OPTIONS => [
            'pim_api_attribute_options_edit',
        ],
    ];

    private const SCOPE_MESSAGE_MAP = [
        self::SCOPE_READ_ATTRIBUTE_OPTIONS => [
            'icon' => 'attribute_options',
            'type' => 'view',
            'entities' => 'attribute_options',
        ],
        self::SCOPE_WRITE_ATTRIBUTE_OPTIONS => [
            'icon' => 'attribute_options',
            'type' => 'edit',
            'entities' => 'attribute_options',
        ],
    ];

    private const SCOPE_HIERARCHY = [
        self::SCOPE_WRITE_ATTRIBUTE_OPTIONS => [
            self::SCOPE_READ_ATTRIBUTE_OPTIONS,
        ],
    ];

    public function getAllScopes(): array
    {
        return [
            self::SCOPE_READ_ATTRIBUTE_OPTIONS,
            self::SCOPE_WRITE_ATTRIBUTE_OPTIONS,
        ];
    }

    public function getAcls(string $scopeName): array
    {
        if (!\array_key_exists($scopeName, self::SCOPE_ACL_MAP)) {
            return [];
        }

        return self::SCOPE_ACL_MAP[$scopeName];
    }

    public function getMessage(string $scope): array
    {
        if (!\array_key_exists($scope, self::SCOPE_MESSAGE_MAP)) {
            return [];
        }

        return self::SCOPE_MESSAGE_MAP[$scope];
    }

    public function getLowerHierarchyScopes(string $scope): array
    {
        if (!\array_key_exists($scope, self::SCOPE_HIERARCHY)) {
            return [];
        }

        return self::SCOPE_HIERARCHY[$scope];
    }
}

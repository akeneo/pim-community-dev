<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Security;

use Akeneo\Tool\Component\Api\Security\ScopeMapperInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceEntityScopeMapper implements ScopeMapperInterface
{
    private const SCOPE_READ_ENTITIES = 'read_entities';
    private const SCOPE_WRITE_ENTITIES = 'write_entities';
    private const SCOPE_READ_ENTITY_RECORDS = 'read_entity_records';
    private const SCOPE_WRITE_ENTITY_RECORDS = 'write_entity_records';

    private const SCOPE_ACL_MAP = [
        self::SCOPE_READ_ENTITIES => [
            'pim_api_reference_entity_list',
        ],
        self::SCOPE_WRITE_ENTITIES => [
            'pim_api_reference_entity_edit',
        ],
        self::SCOPE_READ_ENTITY_RECORDS => [
            'pim_api_reference_entity_record_list',
        ],
        self::SCOPE_WRITE_ENTITY_RECORDS => [
            'pim_api_reference_entity_record_edit',
        ],
    ];

    private const SCOPE_MESSAGE_MAP = [
        self::SCOPE_READ_ENTITIES => [
            'icon' => 'reference_entity',
            'type' => 'view',
            'entities' => 'reference_entity',
        ],
        self::SCOPE_WRITE_ENTITIES => [
            'icon' => 'reference_entity',
            'type' => 'edit',
            'entities' => 'reference_entity',
        ],
        self::SCOPE_READ_ENTITY_RECORDS => [
            'icon' => 'reference_entity_record',
            'type' => 'view',
            'entities' => 'reference_entity_record',
        ],
        self::SCOPE_WRITE_ENTITY_RECORDS => [
            'icon' => 'reference_entity_record',
            'type' => 'edit',
            'entities' => 'reference_entity_record',
        ],
    ];

    private const SCOPE_HIERARCHY = [
        self::SCOPE_WRITE_ENTITIES => [
            self::SCOPE_READ_ENTITIES,
        ],
        self::SCOPE_WRITE_ENTITY_RECORDS => [
            self::SCOPE_READ_ENTITY_RECORDS,
        ],
    ];

    public function getAllScopes(): array
    {
        return [
            self::SCOPE_READ_ENTITIES,
            self::SCOPE_WRITE_ENTITIES,
            self::SCOPE_READ_ENTITY_RECORDS,
            self::SCOPE_WRITE_ENTITY_RECORDS,
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

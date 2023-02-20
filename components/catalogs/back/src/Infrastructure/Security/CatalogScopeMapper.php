<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CatalogScopeMapper implements ScopeMapperInterface
{
    private const SCOPE_READ_CATALOGS = 'read_catalogs';
    private const SCOPE_WRITE_CATALOGS = 'write_catalogs';
    private const SCOPE_DELETE_CATALOGS = 'delete_catalogs';

    private const SCOPE_ACL_MAP = [
        self::SCOPE_READ_CATALOGS => [
            'pim_api_catalog_list',
        ],
        self::SCOPE_WRITE_CATALOGS => [
            'pim_api_catalog_edit',
        ],
        self::SCOPE_DELETE_CATALOGS => [
            'pim_api_catalog_remove',
        ],
    ];

    private const SCOPE_MESSAGE_MAP = [
        self::SCOPE_READ_CATALOGS => [
            'icon' => 'catalogs',
            'type' => 'view',
            'entities' => 'catalogs',
        ],
        self::SCOPE_WRITE_CATALOGS => [
            'icon' => 'catalogs',
            'type' => 'edit',
            'entities' => 'catalogs',
        ],
        self::SCOPE_DELETE_CATALOGS => [
            'icon' => 'catalogs',
            'type' => 'delete',
            'entities' => 'catalogs',
        ],
    ];

    private const SCOPE_HIERARCHY = [
        self::SCOPE_WRITE_CATALOGS => [
            self::SCOPE_READ_CATALOGS,
        ],
        self::SCOPE_DELETE_CATALOGS => [
            self::SCOPE_READ_CATALOGS,
            self::SCOPE_WRITE_CATALOGS,
        ],
    ];

    public function __construct(
        private FeatureFlags $featureFlags,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getScopes(): array
    {
        if (!$this->featureFlags->isEnabled('catalogs')) {
            return [];
        }

        return [
            self::SCOPE_READ_CATALOGS,
            self::SCOPE_WRITE_CATALOGS,
            self::SCOPE_DELETE_CATALOGS,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getAcls(string $scopeName): array
    {
        if (!$this->featureFlags->isEnabled('catalogs')) {
            return [];
        }

        return self::SCOPE_ACL_MAP[$scopeName] ?? [];
    }

    /**
     * {@inheritDoc}
     */
    public function getMessage(string $scopeName): ?array
    {
        if (!$this->featureFlags->isEnabled('catalogs')) {
            return null;
        }

        return self::SCOPE_MESSAGE_MAP[$scopeName] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function getLowerHierarchyScopes(string $scopeName): array
    {
        if (!$this->featureFlags->isEnabled('catalogs')) {
            return [];
        }

        return self::SCOPE_HIERARCHY[$scopeName] ?? [];
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoriesScopeMapper implements ScopeMapperInterface
{
    private const SCOPE_READ_CATEGORIES = 'read_categories';
    private const SCOPE_WRITE_CATEGORIES = 'write_categories';

    private const SCOPE_ACL_MAP = [
        self::SCOPE_READ_CATEGORIES => [
            'pim_api_category_list',
        ],
        self::SCOPE_WRITE_CATEGORIES => [
            'pim_api_category_edit',
        ],
    ];

    private const SCOPE_MESSAGE_MAP = [
        self::SCOPE_READ_CATEGORIES => [
            'icon' => 'categories',
            'type' => 'view',
            'entities' => 'categories',
        ],
        self::SCOPE_WRITE_CATEGORIES => [
            'icon' => 'categories',
            'type' => 'edit',
            'entities' => 'categories',
        ],
    ];

    private const SCOPE_HIERARCHY = [
        self::SCOPE_READ_CATEGORIES => [],
        self::SCOPE_WRITE_CATEGORIES => [
            self::SCOPE_READ_CATEGORIES,
        ],
    ];

    public function getScopes(): array
    {
        return [
            self::SCOPE_READ_CATEGORIES,
            self::SCOPE_WRITE_CATEGORIES,
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

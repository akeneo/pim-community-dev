<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\Security;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ScopeMapper
{
    private const SCOPE_READ_CATALOG_STRUCTURE = 'read_catalog_structure';
    private const SCOPE_WRITE_CATALOG_STRUCTURE = 'write_catalog_structure';
    private const SCOPE_READ_ATTRIBUTE_OPTIONS = 'read_attribute_options';
    private const SCOPE_WRITE_ATTRIBUTE_OPTIONS = 'write_attribute_options';
    private const SCOPE_READ_CATEGORIES = 'read_categories';
    private const SCOPE_WRITE_CATEGORIES = 'write_categories';
    private const SCOPE_READ_CHANNEL_LOCALIZATION = 'read_channel_localization';
    private const SCOPE_READ_CHANNEL_SETTINGS = 'read_channel_settings';
    private const SCOPE_WRITE_CHANNEL_SETTINGS = 'write_channel_settings';
    private const SCOPE_READ_ASSOCIATION_TYPES = 'read_association_types';
    private const SCOPE_WRITE_ASSOCIATION_TYPES = 'write_association_types';
    private const SCOPE_READ_PRODUCTS = 'read_products';
    private const SCOPE_WRITE_PRODUCTS = 'write_products';
    private const SCOPE_DELETE_PRODUCTS = 'delete_products';

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
        self::SCOPE_READ_ATTRIBUTE_OPTIONS => [
            'pim_api_attribute_option_list',
        ],
        self::SCOPE_WRITE_ATTRIBUTE_OPTIONS => [
            'pim_api_attribute_option_edit',
        ],
        self::SCOPE_READ_CATEGORIES => [
            'pim_api_category_list',
        ],
        self::SCOPE_WRITE_CATEGORIES => [
            'pim_api_category_edit',
        ],
        self::SCOPE_READ_CHANNEL_LOCALIZATION => [
            'pim_api_locale_list',
            'pim_api_currency_list',
        ],
        self::SCOPE_READ_CHANNEL_SETTINGS => [
            'pim_api_channel_list',
        ],
        self::SCOPE_WRITE_CHANNEL_SETTINGS => [
            'pim_api_channel_edit',
        ],
        self::SCOPE_READ_ASSOCIATION_TYPES => [
            'pim_api_association_type_list',
        ],
        self::SCOPE_WRITE_ASSOCIATION_TYPES => [
            'pim_api_association_type_edit',
        ],
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

    // scopes whose ACLs are automatically added, by inheritance
    private const SCOPE_HIERARCHY = [
        self::SCOPE_WRITE_CATALOG_STRUCTURE => [
            self::SCOPE_READ_CATALOG_STRUCTURE,
        ],
        self::SCOPE_WRITE_ATTRIBUTE_OPTIONS => [
            self::SCOPE_READ_ATTRIBUTE_OPTIONS,
        ],
        self::SCOPE_WRITE_CATEGORIES => [
            self::SCOPE_READ_CATEGORIES,
        ],
        self::SCOPE_WRITE_CHANNEL_SETTINGS => [
            self::SCOPE_READ_CHANNEL_SETTINGS,
        ],
        self::SCOPE_WRITE_ASSOCIATION_TYPES => [
            self::SCOPE_READ_ASSOCIATION_TYPES,
        ],
        self::SCOPE_WRITE_PRODUCTS => [
            self::SCOPE_READ_PRODUCTS,
        ],
        self::SCOPE_DELETE_PRODUCTS => [
            self::SCOPE_READ_PRODUCTS,
            self::SCOPE_WRITE_PRODUCTS,
        ],
    ];

    /**
     * @return string[]
     */
    public function getAllScopes(): array
    {
        return array_keys(self::SCOPE_ACL_MAP);
    }

    /**
     * @return string[]
     */
    public function getAcls(string $scopeName): array
    {
        if (!isset(self::SCOPE_ACL_MAP[$scopeName])) {
            throw new \LogicException(sprintf('Unknown scope "%s"', $scopeName));
        }

        $acls = self::SCOPE_ACL_MAP[$scopeName];

        foreach ($this->getScopeHierarchy($scopeName) as $inheritedScopeName) {
            $inheritedAcls = $this->getAcls($inheritedScopeName);
            foreach ($inheritedAcls as $inheritedAcl) {
                $acls[] = $inheritedAcl;
            }
        }

        $acls = \array_unique($acls);
        \sort($acls);

        return $acls;
    }

    /**
     * @return string[]
     */
    private function getScopeHierarchy(string $scopeName): array
    {
        if (!isset(self::SCOPE_HIERARCHY[$scopeName])) {
            return [];
        }

        return self::SCOPE_HIERARCHY[$scopeName];
    }

    /**
     * @return string[]
     */
    public function formalizeScopes(array $scopes): array
    {
        $scopes = $this->filterInheritedScopes($scopes);
        \sort($scopes);

        return \array_values(\array_unique($scopes));
    }

    /**
     * @return string[]
     */
    private function filterInheritedScopes(array $scopes): array
    {
        $inheritedScopes = [];

        foreach ($scopes as $scope) {
            $scopeHierarchy = $this->getScopeHierarchy($scope);
            foreach ($scopeHierarchy as $inheritedScope) {
                $inheritedScopes[] = $inheritedScope;
            }
        }

        return \array_filter($scopes, fn($scope) => !in_array($scope, $inheritedScopes));
    }

    public function getMessages(string $scopes): array
    {
        $scopeList = empty($scopes) ? [] : explode(' ', $scopes);
        $scopeList = $this->filterScopeOverlap($scopeList);

        $messages = [];

        foreach ($scopeList as $scope) {
            if ($scope === self::SCOPE_WRITE_CHANNEL_SETTINGS) {
                $messages[] = [
                    'icon' => 'channels',
                    'type' => 'edit',
                    'entities' => 'channels',
                ];
                $messages[] = [
                    'icon' => 'locales_currencies',
                    'type' => 'view',
                    'entities' => 'locales_currencies',
                ];
            } else {
                $messages[] = [
                    'icon' => $this->getIcon($scope),
                    'type' => $this->getType($scope),
                    'entities' => $this->getEntities($scope),
                ];
            }
        }

        return $messages;
    }

    private function getIcon(string $scope): string
    {
        switch ($scope) {
            case self::SCOPE_READ_CATALOG_STRUCTURE:
            case self::SCOPE_WRITE_CATALOG_STRUCTURE:
                return 'catalog_structure';
            case self::SCOPE_READ_ATTRIBUTE_OPTIONS:
            case self::SCOPE_WRITE_ATTRIBUTE_OPTIONS:
                return 'attribute_options';
            case self::SCOPE_READ_CATEGORIES:
            case self::SCOPE_WRITE_CATEGORIES:
                return 'categories';
            case self::SCOPE_READ_CHANNEL_SETTINGS:
            case self::SCOPE_WRITE_CHANNEL_SETTINGS:
                return 'channels';
            case self::SCOPE_READ_ASSOCIATION_TYPES:
            case self::SCOPE_WRITE_ASSOCIATION_TYPES:
                return 'association_types';
            case self::SCOPE_READ_PRODUCTS:
            case self::SCOPE_WRITE_PRODUCTS:
            case self::SCOPE_DELETE_PRODUCTS:
                return 'products';
            default:
                return 'unknown';
        }
    }

    private function getType(string $scope): string
    {
        switch ($scope) {
            case self::SCOPE_READ_CATALOG_STRUCTURE:
            case self::SCOPE_READ_ATTRIBUTE_OPTIONS:
            case self::SCOPE_READ_CATEGORIES:
            case self::SCOPE_READ_CHANNEL_SETTINGS:
            case self::SCOPE_READ_ASSOCIATION_TYPES:
            case self::SCOPE_READ_PRODUCTS:
                return 'view';
            case self::SCOPE_WRITE_CATALOG_STRUCTURE:
            case self::SCOPE_WRITE_ATTRIBUTE_OPTIONS:
            case self::SCOPE_WRITE_CATEGORIES:
            case self::SCOPE_WRITE_CHANNEL_SETTINGS:
            case self::SCOPE_WRITE_ASSOCIATION_TYPES:
            case self::SCOPE_WRITE_PRODUCTS:
                return 'edit';
            case self::SCOPE_DELETE_PRODUCTS:
                return 'delete';
            default:
                return 'unknown';
        }
    }

    private function getEntities(string $scope): string
    {
        switch ($scope) {
            case self::SCOPE_READ_CATALOG_STRUCTURE:
            case self::SCOPE_WRITE_CATALOG_STRUCTURE:
                return 'catalog_structure';
            case self::SCOPE_READ_ATTRIBUTE_OPTIONS:
            case self::SCOPE_WRITE_ATTRIBUTE_OPTIONS:
                return 'attribute_options';
            case self::SCOPE_READ_CATEGORIES:
            case self::SCOPE_WRITE_CATEGORIES:
                return 'categories';
            case self::SCOPE_READ_CHANNEL_SETTINGS:
            case self::SCOPE_WRITE_CHANNEL_SETTINGS:
                return 'channel_settings';
            case self::SCOPE_READ_ASSOCIATION_TYPES:
            case self::SCOPE_WRITE_ASSOCIATION_TYPES:
                return 'association_types';
            case self::SCOPE_READ_PRODUCTS:
            case self::SCOPE_WRITE_PRODUCTS:
            case self::SCOPE_DELETE_PRODUCTS:
                return 'products';
            default:
                return 'unknown';
        }
    }

    /**
     * @param array<string> $scopeList
     * @return array<string>
     */
    private function filterScopeOverlap(array $scopeList): array
    {
        return array_filter($scopeList, function ($scope) use ($scopeList) {
            switch ($scope) {
                case self::SCOPE_READ_CATALOG_STRUCTURE:
                    return !in_array(self::SCOPE_WRITE_CATALOG_STRUCTURE, $scopeList);
                case self::SCOPE_READ_ATTRIBUTE_OPTIONS:
                    return !in_array(self::SCOPE_WRITE_ATTRIBUTE_OPTIONS, $scopeList);
                case self::SCOPE_READ_CATEGORIES:
                    return !in_array(self::SCOPE_WRITE_CATEGORIES, $scopeList);
                case self::SCOPE_READ_CHANNEL_SETTINGS:
                    return !in_array(self::SCOPE_WRITE_CHANNEL_SETTINGS, $scopeList);
                case self::SCOPE_READ_ASSOCIATION_TYPES:
                    return !in_array(self::SCOPE_WRITE_ASSOCIATION_TYPES, $scopeList);
                case self::SCOPE_READ_PRODUCTS:
                    return !in_array(self::SCOPE_WRITE_PRODUCTS, $scopeList)
                        || !in_array(self::SCOPE_WRITE_PRODUCTS, $scopeList);
                case self::SCOPE_WRITE_PRODUCTS:
                    return !in_array(self::SCOPE_DELETE_PRODUCTS, $scopeList);
                default:
                    return true;
            }
        });
    }
}

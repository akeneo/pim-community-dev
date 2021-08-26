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

    private const ENTITY_CATALOG_STRUCTURE = 'catalog_structure';
    private const ENTITY_ATTRIBUTE_OPTIONS = 'attribute_options';
    private const ENTITY_CATEGORIES = 'categories';
    private const ENTITY_CHANNEL_SETTINGS = 'channel_settings';
    private const ENTITY_CHANNEL_LOCALIZATION = 'channel_localization';
    private const ENTITY_ASSOCIATION_TYPES = 'association_types';
    private const ENTITY_PRODUCTS = 'products';

    private const TYPE_VIEW = 'view';
    private const TYPE_EDIT = 'edit';
    private const TYPE_DELETE = 'delete';

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

    private const SCOPE_ENTITY_MAP = [
        self::SCOPE_READ_CATALOG_STRUCTURE => self::ENTITY_CATALOG_STRUCTURE,
        self::SCOPE_WRITE_CATALOG_STRUCTURE => self::ENTITY_CATALOG_STRUCTURE,
        self::SCOPE_READ_ATTRIBUTE_OPTIONS => self::ENTITY_ATTRIBUTE_OPTIONS,
        self::SCOPE_WRITE_ATTRIBUTE_OPTIONS => self::ENTITY_ATTRIBUTE_OPTIONS,
        self::SCOPE_READ_CATEGORIES => self::ENTITY_CATEGORIES,
        self::SCOPE_WRITE_CATEGORIES => self::ENTITY_CATEGORIES,
        self::SCOPE_READ_CHANNEL_LOCALIZATION => self::ENTITY_CHANNEL_LOCALIZATION,
        self::SCOPE_READ_CHANNEL_SETTINGS => self::ENTITY_CHANNEL_SETTINGS,
        self::SCOPE_WRITE_CHANNEL_SETTINGS => self::ENTITY_CHANNEL_SETTINGS,
        self::SCOPE_READ_ASSOCIATION_TYPES => self::ENTITY_ASSOCIATION_TYPES,
        self::SCOPE_WRITE_ASSOCIATION_TYPES => self::ENTITY_ASSOCIATION_TYPES,
        self::SCOPE_READ_PRODUCTS => self::ENTITY_PRODUCTS,
        self::SCOPE_WRITE_PRODUCTS => self::ENTITY_PRODUCTS,
        self::SCOPE_DELETE_PRODUCTS => self::ENTITY_PRODUCTS,
    ];

    private const SCOPE_TYPE_MAP = [
        self::SCOPE_READ_CATALOG_STRUCTURE => self::TYPE_VIEW,
        self::SCOPE_WRITE_CATALOG_STRUCTURE => self::TYPE_EDIT,
        self::SCOPE_READ_ATTRIBUTE_OPTIONS => self::TYPE_VIEW,
        self::SCOPE_WRITE_ATTRIBUTE_OPTIONS => self::TYPE_EDIT,
        self::SCOPE_READ_CATEGORIES => self::TYPE_VIEW,
        self::SCOPE_WRITE_CATEGORIES => self::TYPE_EDIT,
        self::SCOPE_READ_CHANNEL_LOCALIZATION => self::TYPE_VIEW,
        self::SCOPE_READ_CHANNEL_SETTINGS => self::TYPE_VIEW,
        self::SCOPE_WRITE_CHANNEL_SETTINGS => self::TYPE_EDIT,
        self::SCOPE_READ_ASSOCIATION_TYPES => self::TYPE_VIEW,
        self::SCOPE_WRITE_ASSOCIATION_TYPES => self::TYPE_EDIT,
        self::SCOPE_READ_PRODUCTS => self::TYPE_VIEW,
        self::SCOPE_WRITE_PRODUCTS => self::TYPE_EDIT,
        self::SCOPE_DELETE_PRODUCTS => self::TYPE_DELETE,
    ];

    private const SCOPE_ICON_MAP = [
        self::SCOPE_READ_CATALOG_STRUCTURE => self::ENTITY_CATALOG_STRUCTURE,
        self::SCOPE_WRITE_CATALOG_STRUCTURE => self::ENTITY_CATALOG_STRUCTURE,
        self::SCOPE_READ_ATTRIBUTE_OPTIONS => self::ENTITY_ATTRIBUTE_OPTIONS,
        self::SCOPE_WRITE_ATTRIBUTE_OPTIONS => self::ENTITY_ATTRIBUTE_OPTIONS,
        self::SCOPE_READ_CATEGORIES => self::ENTITY_CATEGORIES,
        self::SCOPE_WRITE_CATEGORIES => self::ENTITY_CATEGORIES,
        self::SCOPE_READ_CHANNEL_LOCALIZATION => self::ENTITY_CHANNEL_LOCALIZATION,
        self::SCOPE_READ_CHANNEL_SETTINGS => self::ENTITY_CHANNEL_SETTINGS,
        self::SCOPE_WRITE_CHANNEL_SETTINGS => self::ENTITY_CHANNEL_SETTINGS,
        self::SCOPE_READ_ASSOCIATION_TYPES => self::ENTITY_ASSOCIATION_TYPES,
        self::SCOPE_WRITE_ASSOCIATION_TYPES => self::ENTITY_ASSOCIATION_TYPES,
        self::SCOPE_READ_PRODUCTS => self::ENTITY_PRODUCTS,
        self::SCOPE_WRITE_PRODUCTS => self::ENTITY_PRODUCTS,
        self::SCOPE_DELETE_PRODUCTS => self::ENTITY_PRODUCTS,
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

        return \array_filter($scopes, fn ($scope) => !in_array($scope, $inheritedScopes));
    }

    /**
     * @param string[] $scopeList
     */
    public function getMessages(array $scopeList): array
    {
        $scopeList = $this->formalizeScopes($scopeList);

        $messages = [];

        foreach ($scopeList as $scope) {
            $messages[] = [
                'icon' => $this->getIcon($scope),
                'type' => $this->getType($scope),
                'entities' => $this->getEntities($scope),
            ];
        }

        return $messages;
    }

    private function getIcon(string $scopeName): string
    {
        if (!isset(self::SCOPE_ICON_MAP[$scopeName])) {
            throw new \LogicException(sprintf('Unknown scope "%s"', $scopeName));
        }

        return self::SCOPE_ICON_MAP[$scopeName];
    }

    private function getType(string $scopeName): string
    {
        if (!isset(self::SCOPE_TYPE_MAP[$scopeName])) {
            throw new \LogicException(sprintf('Unknown scope "%s"', $scopeName));
        }

        return self::SCOPE_TYPE_MAP[$scopeName];
    }

    private function getEntities(string $scopeName): string
    {
        if (!isset(self::SCOPE_ENTITY_MAP[$scopeName])) {
            throw new \LogicException(sprintf('Unknown scope "%s"', $scopeName));
        }

        return self::SCOPE_ENTITY_MAP[$scopeName];
    }
}

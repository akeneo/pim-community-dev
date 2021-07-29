<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\Security;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopeToAclMapper
{
    public const SCOPE_READ_CATALOG_STRUCTURE = 'read_catalog_structure';
    public const SCOPE_WRITE_CATALOG_STRUCTURE = 'write_catalog_structure';
    public const SCOPE_READ_EXTENDED_PRODUCT_DATA = 'read_extended_product_data';
    public const SCOPE_WRITE_EXTENDED_PRODUCT_DATA = 'write_extended_product_data';
    public const SCOPE_READ_TARGET_SETTINGS = 'read_target_settings';
    public const SCOPE_WRITE_TARGET_SETTINGS = 'write_target_settings';
    public const SCOPE_READ_ASSOCIATION_TYPES = 'read_association_types';
    public const SCOPE_WRITE_ASSOCIATION_TYPES = 'write_association_types';
    public const SCOPE_WRITE_PRODUCTS = 'write_products';
    public const SCOPE_READ_PRODUCTS = 'read_products';
    public const SCOPE_DELETE_PRODUCTS = 'delete_products';

    public const ScopeAclMap = [
        self::SCOPE_READ_CATALOG_STRUCTURE => [
            'pim_api_attribute_list',
            'pim_api_attribute_group_list',
            'pim_api_family_list',
            'pim_api_family_variant_list',
        ],
        self::SCOPE_WRITE_CATALOG_STRUCTURE => [
            'pim_api_attribute_list',
            'pim_api_attribute_edit',
            'pim_api_attribute_group_list',
            'pim_api_attribute_group_edit',
            'pim_api_family_list',
            'pim_api_family_edit',
            'pim_api_family_variant_list',
            'pim_api_family_variant_edit',
        ],
        self::SCOPE_READ_EXTENDED_PRODUCT_DATA => [
            'pim_api_attribute_option_list',
            'pim_api_category_list',
        ],
        self::SCOPE_WRITE_EXTENDED_PRODUCT_DATA => [
            'pim_api_attribute_option_list',
            'pim_api_attribute_option_edit',
            'pim_api_category_list',
            'pim_api_category_edit',
        ],
        self::SCOPE_READ_TARGET_SETTINGS => [
            'pim_api_channel_list',
            'pim_api_locale_list',
            'pim_api_currency_list',
        ],
        self::SCOPE_WRITE_TARGET_SETTINGS => [
            'pim_api_channel_list',
            'pim_api_channel_edit',
            'pim_api_locale_list',
            'pim_api_currency_list',
        ],
        self::SCOPE_READ_ASSOCIATION_TYPES => [
            'pim_api_association_type_list',
        ],
        self::SCOPE_WRITE_ASSOCIATION_TYPES => [
            'pim_api_association_type_list',
            'pim_api_association_type_edit',
        ],
        self::SCOPE_READ_PRODUCTS => [
            'pim_api_product_list',
        ],
        self::SCOPE_WRITE_PRODUCTS => [
            'pim_api_product_list',
            'pim_api_product_edit',
        ],
        self::SCOPE_DELETE_PRODUCTS => [
            'pim_api_product_list',
            'pim_api_product_edit',
            'pim_api_product_remove',
        ],
    ];

    public function getAllScopes(): array
    {
        return [
            self::SCOPE_READ_CATALOG_STRUCTURE,
            self::SCOPE_WRITE_CATALOG_STRUCTURE,
            self::SCOPE_READ_EXTENDED_PRODUCT_DATA,
            self::SCOPE_WRITE_EXTENDED_PRODUCT_DATA,
            self::SCOPE_READ_TARGET_SETTINGS,
            self::SCOPE_WRITE_TARGET_SETTINGS,
            self::SCOPE_READ_ASSOCIATION_TYPES,
            self::SCOPE_WRITE_ASSOCIATION_TYPES,
            self::SCOPE_WRITE_PRODUCTS,
            self::SCOPE_READ_PRODUCTS,
            self::SCOPE_DELETE_PRODUCTS,
        ];
    }

    public function getAcls(string $scopeName): array
    {
        return self::ScopeAclMap[$scopeName] ?? [];
    }
}

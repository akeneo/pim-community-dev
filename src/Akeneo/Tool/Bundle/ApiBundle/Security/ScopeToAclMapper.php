<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\Security;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ScopeToAclMapper
{
    private const SCOPE_READ_CATALOG_STRUCTURE = 'read_catalog_structure';
    private const SCOPE_WRITE_CATALOG_STRUCTURE = 'write_catalog_structure';
    private const SCOPE_READ_ATTRIBUTE_OPTIONS = 'read_attribute_options';
    private const SCOPE_WRITE_ATTRIBUTE_OPTIONS = 'write_attribute_options';
    private const SCOPE_READ_CATEGORIES = 'read_categories';
    private const SCOPE_WRITE_CATEGORIES = 'write_categories';
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
            'pim_api_attribute_list',
            'pim_api_attribute_edit',
            'pim_api_attribute_group_list',
            'pim_api_attribute_group_edit',
            'pim_api_family_list',
            'pim_api_family_edit',
            'pim_api_family_variant_list',
            'pim_api_family_variant_edit',
        ],
        self::SCOPE_READ_ATTRIBUTE_OPTIONS => [
            'pim_api_attribute_option_list',
        ],
        self::SCOPE_WRITE_ATTRIBUTE_OPTIONS => [
            'pim_api_attribute_option_list',
            'pim_api_attribute_option_edit',
        ],
        self::SCOPE_READ_CATEGORIES => [
            'pim_api_category_list',
        ],
        self::SCOPE_WRITE_CATEGORIES => [
            'pim_api_category_list',
            'pim_api_category_edit',
        ],
        self::SCOPE_READ_CHANNEL_SETTINGS => [
            'pim_api_channel_list',
            'pim_api_locale_list',
            'pim_api_currency_list',
        ],
        self::SCOPE_WRITE_CHANNEL_SETTINGS => [
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

        return self::SCOPE_ACL_MAP[$scopeName];
    }
}

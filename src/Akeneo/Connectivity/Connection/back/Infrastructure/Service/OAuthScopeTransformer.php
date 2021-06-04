<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Service;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OAuthScopeTransformer
{
    public const PRODUCT_EDIT_SCOPE = 'product:edit';
    public const ATTRIBUTE_LIST_SCOPE = 'attribute:list';
    private const PRODUCT_EDIT_ACL = 'action:pim_api_attribute_edit';
    private const ATTRIBUTE_LIST_ACL = 'action:pim_api_attribute_list';

    private static array $scopesToAclMapping = [
        self::PRODUCT_EDIT_SCOPE => self::PRODUCT_EDIT_ACL,
        self::ATTRIBUTE_LIST_SCOPE => self::ATTRIBUTE_LIST_ACL
    ];

    public function transform(array $oauthScopes): array
    {
        $aclPermissions = [];
        foreach ($oauthScopes as $scope) {
            if (isset(self::$scopesToAclMapping[$scope])) {
                $aclPermissions[self::$scopesToAclMapping[$scope]] = true;
            }
        }
        return $aclPermissions;
    }
}
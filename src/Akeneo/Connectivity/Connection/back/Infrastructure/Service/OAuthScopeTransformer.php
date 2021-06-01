<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Service;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OAuthScopeTransformer
{
    private const PRODUCT_EDIT_SCOPE = 'product:edit';
    private const PRODUCT_EDIT_ACL = 'pim_enrich_product_edit_attributes';

    private static array $scopesToAclMapping = [
        self::PRODUCT_EDIT_SCOPE => self::PRODUCT_EDIT_ACL
    ];

    public function transform(array $oauthScopes): array
    {
        $aclPermissions = [];
        foreach ($oauthScopes as $scope) {
            if (isset(self::$scopesToAclMapping[$scope])) {
                $aclPermissions[] = self::$scopesToAclMapping[$scope];
            }
        }
        return $aclPermissions;
    }
}
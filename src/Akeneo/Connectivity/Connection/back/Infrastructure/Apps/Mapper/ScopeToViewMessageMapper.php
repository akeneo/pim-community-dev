<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Mapper;

use Akeneo\Tool\Bundle\ApiBundle\Security\ScopeMapper;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopeToViewMessageMapper
{
    private ScopeMapper $scopeToAclMapper;
    public function __construct(ScopeMapper $scopeToAclMapper)
    {
        $this->scopeToAclMapper = $scopeToAclMapper;
    }

    public function getMessages(string $scopes): array
    {
        $scopeList = empty($scopes) ? [] : explode(' ', $scopes);

        $scopeList = $this->filterScopeOverlap($scopeList);

        $messages = [];

        foreach ($scopeList as $scope) {
            $messages[] = [
                'message' => "akeneo_connectivity.connection.connect.apps.authorize.scope.$scope",
                'icon' => $this->getIcon($scope),
            ];
        }

        return $messages;
    }

    private function getIcon(string $scope): string
    {
        switch ($scope) {
            case 'read_catalog_structure' :
            case 'write_catalog_structure' :
                return 'catalog_structure';
            case 'read_attribute_options' :
            case 'write_attribute_options' :
                return 'attribute';
            case 'read_categories' :
            case 'write_categories' :
                return 'category';
            case 'read_channel_settings' :
            case 'write_channel_settings' :
                return 'channel';
            case 'read_association_types' :
            case 'write_association_types' :
                return 'association_types';
            case 'read_products' :
            case 'write_products' :
            case 'delete_products' :
                return 'product';
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
        return $scopeList;
    }
}

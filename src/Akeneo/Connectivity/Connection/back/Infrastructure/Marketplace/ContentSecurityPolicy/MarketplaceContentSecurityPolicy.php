<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace\ContentSecurityPolicy;

use Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProviderInterface;

/**
 * Add content security policy authorizations for apps and marketplace subdomains
 *
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MarketplaceContentSecurityPolicy implements ContentSecurityPolicyProviderInterface
{
    public function getContentSecurityPolicy(): array
    {
        return [
            'img-src' => [
                'apps.akeneo.com',
                'marketplace.akeneo.com'
            ],
        ];
    }
}

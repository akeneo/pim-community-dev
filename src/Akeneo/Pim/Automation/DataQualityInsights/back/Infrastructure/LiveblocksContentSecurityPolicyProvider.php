<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure;

use Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProviderInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LiveblocksContentSecurityPolicyProvider implements ContentSecurityPolicyProviderInterface
{
    public function getContentSecurityPolicy(): array
    {
        return [
            'connect-src'=> ["*"],
        ];
    }
}

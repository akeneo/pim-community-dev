<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\POC;

final class ContentSecurityProvider
{
    /**
     * @var ContentSecurityPolicyProviderInterface[]
     */
    private iterable $contentSecurityProviders;

    public function __construct(iterable $contentSecurityProviders)
    {
        $this->contentSecurityProviders = $contentSecurityProviders;
    }

    public function getPolicy(): string
    {
        $policies = [];
        foreach ($this->contentSecurityProviders as $contentSecurityProvider) {
            $policies = array_merge_recursive($policies, $contentSecurityProvider->getContentSecurityPolicy());
        }

        $policiesAsString = [];
        foreach ($policies as $directive => $policy) {
            $policiesAsString[] = $directive . ' ' . join(' ', array_unique($policy));
        }

        return join('; ', $policiesAsString);
    }
}

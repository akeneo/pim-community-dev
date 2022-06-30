<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy;

use Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProviderInterface;

final class ContentSecurityPolicyProvider
{
    /**
     * @var ContentSecurityPolicyProviderInterface[]
     */
    private iterable $contentSecurityPolicyProviders;

    public function __construct(iterable $contentSecurityPolicyProviders)
    {
        $this->contentSecurityPolicyProviders = $contentSecurityPolicyProviders;
    }

    public function getPolicy(): string
    {
        $policies = [];
        foreach ($this->contentSecurityPolicyProviders as $contentSecurityPolicyProvider) {
            $policies = array_merge_recursive($policies, $contentSecurityPolicyProvider->getContentSecurityPolicy());
        }

        $policiesAsString = [];
        foreach ($policies as $directive => $policy) {
            $policiesAsString[] = $directive . ' ' . join(' ', array_unique($policy));
        }

        return join('; ', $policiesAsString);
    }
}
